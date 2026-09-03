<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute as ProductAttribute;
use App\Models\Order;
use App\Models\WalletTransaction;
use App\Services\OrderCancellationNotifier;
use App\Services\OrderInventoryService;
use App\Support\OrderCancellationReasons;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => [
                'nullable',
                Rule::in([
                    'pending',
                    'confirmed',
                    'processing',
                    'shipping',
                    'completed',
                    'cancelled',
                ]),
            ],
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', Rule::in([10, 20, 50])],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 10);

        $query = Order::with('user');

        if (! empty($validated['status'])) {
            $query->where('order_status', $validated['status']);
        }

        if (($validated['status'] ?? null) === 'confirmed') {
            $query->orderByRaw('packing_slip_printed_at IS NULL DESC');
        }

        $query->orderByDesc('created_at')
            ->orderByDesc('id');

        if (! empty($validated['search'])) {
            $search = trim($validated['search']);

            $query->where(function ($query) use ($search): void {
                $query->where('order_code', 'like', '%'.$search.'%')
                    ->orWhere('customer_name', 'like', '%'.$search.'%')
                    ->orWhere('customer_phone', 'like', '%'.$search.'%');
            });
        }

        $orders = $query
            ->paginate($perPage)
            ->withQueryString();

        $adminCancellationReasons = OrderCancellationReasons::admin();

        return view(
            'admin.orders.index',
            compact('orders', 'adminCancellationReasons')
        );
    }

    public function show(Order $order)
    {
        $order->load(
            'details.variant.product',
            'user'
        );

        $adminCancellationReasons = OrderCancellationReasons::admin();

        return view(
            'admin.orders.show',
            compact('order', 'adminCancellationReasons')
        );
    }

    public function packingSlips(Request $request)
    {
        $validated = $request->validate(
            [
                'order_ids' => ['required', 'array', 'min:1', 'max:100'],
                'order_ids.*' => [
                    'required',
                    'integer',
                    'distinct',
                    'exists:orders,id',
                ],
            ],
            [
                'order_ids.required' => 'Vui lòng chọn ít nhất một đơn hàng để in phiếu.',
                'order_ids.min' => 'Vui lòng chọn ít nhất một đơn hàng để in phiếu.',
                'order_ids.max' => 'Mỗi lần chỉ có thể in tối đa 100 phiếu đóng hàng.',
                'order_ids.*.distinct' => 'Danh sách đơn hàng được chọn đang bị trùng.',
                'order_ids.*.exists' => 'Có đơn hàng được chọn không còn tồn tại.',
            ]
        );

        $orderIds = collect($validated['order_ids'])
            ->map(fn ($orderId): int => (int) $orderId)
            ->values();

        $ordersById = Order::with('details.variant.product')
            ->whereIn('id', $orderIds)
            ->get()
            ->keyBy('id');

        if (
            $ordersById->count() !== $orderIds->count()
            || $ordersById->contains(
                fn (Order $order): bool => $order->order_status !== 'confirmed'
            )
        ) {
            return back()->with(
                'error',
                'Chỉ có thể in phiếu đóng hàng cho đơn đang ở trạng thái Đã xác nhận.'
            );
        }

        Order::whereIn('id', $orderIds)
            ->whereNull('packing_slip_printed_at')
            ->update(['packing_slip_printed_at' => now()]);

        $attributeValueLookup = $this->packingAttributeValueLookup();

        $packingOrders = $orderIds->map(function (int $orderId) use (
            $ordersById,
            $attributeValueLookup
        ): array {
            $order = $ordersById->get($orderId);

            return [
                'code' => $order->order_code,
                'items' => $order->details->map(function ($detail) use (
                    $attributeValueLookup
                ): array {
                    return [
                        'name' => $detail->variant?->product?->name
                            ?: 'Sản phẩm',
                        'options' => $this->packingVariantOptions(
                            $detail->variant?->name,
                            $attributeValueLookup
                        ),
                        'quantity' => (int) $detail->quantity,
                    ];
                }),
            ];
        });

        return view(
            'admin.orders.packing-slips',
            compact('packingOrders')
        );
    }

    public function updateStatus(
        Request $request,
        Order $order,
        OrderCancellationNotifier $notifier,
        OrderInventoryService $inventoryService
    ) {
        $rules = [
            'order_status' => [
                'required',
                Rule::in([
                    'pending',
                    'confirmed',
                    'processing',
                    'shipping',
                    'completed',
                    'cancelled',
                ]),
            ],
        ];

        if ($request->input('order_status') === 'cancelled') {
            $rules['cancellation_reason'] = [
                'required',
                Rule::in(array_keys(OrderCancellationReasons::admin())),
            ];

            $rules['cancellation_note'] = [
                'nullable',
                'string',
                'max:1000',
                Rule::requiredIf(
                    $request->input('cancellation_reason') === 'other'
                ),
            ];
        }

        $validated = $request->validate(
            $rules,
            [
                'cancellation_reason.required' => 'Vui lòng chọn lý do hủy đơn hàng.',

                'cancellation_note.required' => 'Vui lòng nhập ghi chú cho lý do hủy đơn hàng.',
            ]
        );

        $currentStatus = $order->order_status;
        $newStatus = $validated['order_status'];

        /*
        |--------------------------------------------------------------------------
        | Không cho thay đổi trạng thái cuối
        |--------------------------------------------------------------------------
        */
        if (in_array(
            $currentStatus,
            ['completed', 'cancelled'],
            true
        )) {
            return back()->with(
                'error',
                'Không thể thay đổi trạng thái của đơn hàng đã Hoàn thành hoặc Đã hủy.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Luồng trạng thái đơn hàng
        |--------------------------------------------------------------------------
        |
        | Luồng mới:
        |
        | pending
        |    ↓
        | confirmed
        |    ↓
        | shipping
        |    ↓
        | completed
        |
        | processing vẫn được giữ để tương thích với đơn hàng cũ.
        |
        */
        $validTransitions = [
            'pending' => [
                'confirmed',
                'cancelled',
            ],

            'confirmed' => [
                'shipping',
                'cancelled',
            ],

            'processing' => [
                'shipping',
                'cancelled',
            ],

            'shipping' => [
                'completed',
                'cancelled',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Không cho hoàn thành nếu chưa thanh toán
        |--------------------------------------------------------------------------
        */
        if (
            $newStatus === 'completed'
            && $order->payment_status !== 'paid'
        ) {
            return back()->with(
                'error',
                'Đơn hàng phải được thanh toán trước khi hoàn thành.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Kiểm tra chuyển trạng thái hợp lệ
        |--------------------------------------------------------------------------
        */
        if (! in_array(
            $newStatus,
            $validTransitions[$currentStatus] ?? [],
            true
        )) {
            return back()->with(
                'error',
                'Trạng thái chuyển đổi không hợp lệ.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Xác nhận đơn COD
        |--------------------------------------------------------------------------
        |
        | Khi admin xác nhận đơn COD:
        |
        | - OrderInventoryService xử lý tồn kho.
        | - Kiểm tra timeout 24 giờ.
        | - Nếu đơn đã quá hạn thì service có thể tự hủy và nhả hàng.
        |
        */
        if (
            $newStatus === 'confirmed'
            && $order->payment_method === 'cod'
        ) {
            try {
                $order = $inventoryService->confirmCod($order);
            } catch (DomainException $e) {
                return back()->with(
                    'error',
                    $e->getMessage()
                );
            }

            /*
            | Nếu service phát hiện đơn COD quá hạn
            | và tự chuyển sang cancelled.
            */
            if ($order->order_status === 'cancelled') {
                $notifier->send($order);

                return back()->with(
                    'error',
                    'Đơn COD đã quá hạn 24 giờ nên hệ thống đã tự hủy và nhả hàng.'
                );
            }

            return back()->with(
                'success',
                'Đã xác nhận đơn COD và trừ số lượng khỏi kho.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Dữ liệu cập nhật đơn hàng
        |--------------------------------------------------------------------------
        */
        $updateData = [
            'order_status' => $newStatus,
        ];

        /*
        |--------------------------------------------------------------------------
        | Hủy đơn hàng
        |--------------------------------------------------------------------------
        */
        if ($newStatus === 'cancelled') {
            $updateData += [
                'cancelled_by' => 'admin',

                'cancellation_reason' => $validated['cancellation_reason'],

                'cancellation_note' => $validated['cancellation_reason'] === 'other'
                        ? trim($validated['cancellation_note'])
                        : null,

                'cancelled_at' => now(),
            ];

            /*
            |--------------------------------------------------------------------------
            | Hoàn tiền
            |--------------------------------------------------------------------------
            |
            | Nếu đơn đã thanh toán bằng VietQR hoặc ví
            | thì hoàn tiền vào ví khách hàng.
            |
            */
            if (
                $order->payment_status === 'paid'
                && in_array(
                    $order->payment_method,
                    ['vietqr', 'wallet'],
                    true
                )
            ) {
                $user = $order->user;

                if ($user) {
                    $user->increment(
                        'wallet_balance',
                        $order->total_amount
                    );

                    WalletTransaction::create([
                        'user_id' => $user->id,
                        'type' => 'refund',
                        'amount' => $order->total_amount,
                        'description' => 'Hoàn tiền do Admin hủy đơn hàng #'
                            .$order->order_code,
                    ]);
                }
            }

        }

        /*
        |--------------------------------------------------------------------------
        | Thực hiện cập nhật
        |--------------------------------------------------------------------------
        */
        try {
            if ($newStatus === 'cancelled') {
                /*
                | Service chịu trách nhiệm:
                |
                | - cập nhật trạng thái
                | - hoàn / nhả tồn kho
                | - tránh cộng kho hai lần
                */
                $order = $inventoryService->cancel(
                    $order,
                    $updateData
                );
            } else {
                $order->update($updateData);
            }
        } catch (DomainException $e) {
            return back()->with(
                'error',
                $e->getMessage()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Gửi thông báo hủy đơn
        |--------------------------------------------------------------------------
        */
        if ($newStatus === 'cancelled') {
            $notifier->send($order);
        }

        return back()->with(
            'success',
            'Đã cập nhật trạng thái đơn hàng thành công.'
        );
    }

    public function updatePaymentStatus(
        Request $request,
        Order $order
    ) {
        $validated = $request->validate([
            'payment_status' => [
                'required',
                Rule::in([
                    'paid',
                    'failed',
                    'pending',
                ]),
            ],
        ]);

        if ($order->payment_status === 'paid') {
            return back()->with(
                'error',
                'Đơn hàng này đã được thanh toán trước đó.'
            );
        }

        $order->update([
            'payment_status' => $validated['payment_status'],
        ]);

        return back()->with(
            'success',
            'Đã cập nhật trạng thái thanh toán thành công.'
        );
    }

    /**
     * @return array<string, array{attribute: string, value: string}>
     */
    private function packingAttributeValueLookup(): array
    {
        $lookup = [];

        ProductAttribute::with('values')
            ->orderBy('id')
            ->get()
            ->each(function (ProductAttribute $attribute) use (&$lookup): void {
                foreach ($attribute->values as $value) {
                    $key = Str::lower(trim((string) $value->value));

                    if ($key === '' || isset($lookup[$key])) {
                        continue;
                    }

                    $lookup[$key] = [
                        'attribute' => $this->packingAttributeLabel(
                            (string) $attribute->name
                        ),
                        'value' => (string) $value->value,
                    ];
                }
            });

        return $lookup;
    }

    /**
     * @param  array<string, array{attribute: string, value: string}>  $lookup
     * @return array<string, string>
     */
    private function packingVariantOptions(
        ?string $variantName,
        array $lookup
    ): array {
        $variantName = trim((string) $variantName);

        if ($variantName === '' || Str::lower($variantName) === 'mặc định') {
            return [];
        }

        $options = [];
        $unmatchedParts = [];
        $parts = preg_split('/\s+-\s+/u', $variantName) ?: [];

        foreach ($parts as $part) {
            $part = trim($part);

            if ($part === '') {
                continue;
            }

            $matchedValue = $lookup[Str::lower($part)] ?? null;

            if ($matchedValue) {
                $options[$matchedValue['attribute']] = $matchedValue['value'];
            } else {
                $unmatchedParts[] = $part;
            }
        }

        if ($unmatchedParts !== []) {
            $options['Phân loại'] = implode(' - ', $unmatchedParts);
        }

        return $options;
    }

    private function packingAttributeLabel(string $attributeName): string
    {
        $normalizedName = Str::lower(Str::ascii(trim($attributeName)));

        if (
            $normalizedName === 'size'
            || Str::contains($normalizedName, 'kich co')
        ) {
            return 'Size';
        }

        if (
            $normalizedName === 'color'
            || Str::contains($normalizedName, 'mau')
        ) {
            return 'Màu';
        }

        return trim($attributeName) ?: 'Phân loại';
    }
}
