<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\WalletTransaction;
use App\Services\OrderCancellationNotifier;
use App\Services\OrderInventoryService;
use App\Support\OrderCancellationReasons;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with([
            'details.variant.product',
            'details.review',
        ])
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(8)
            ->withQueryString()
            ->fragment('orders');

        $customerCancellationReasons =
            OrderCancellationReasons::customer();

        return view(
            'client.orders.index',
            compact(
                'orders',
                'customerCancellationReasons'
            )
        );
    }

    public function show($id)
    {
        $order = Order::with([
            'details.variant.product',
            'details.review',
        ])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        $customerCancellationReasons =
            OrderCancellationReasons::customer();

        return view(
            'client.orders.show',
            compact(
                'order',
                'customerCancellationReasons'
            )
        );
    }

    public function cancel(
        Request $request,
        OrderCancellationNotifier $notifier,
        OrderInventoryService $inventoryService,
        $id
    ) {
        $validated = $request->validate([
            'cancellation_reason' => [
                'required',
                Rule::in(
                    array_keys(
                        OrderCancellationReasons::customer()
                    )
                ),
            ],

            'cancellation_note' => [
                'nullable',
                'string',
                'max:1000',
                Rule::requiredIf(
                    $request->input(
                        'cancellation_reason'
                    ) === 'other'
                ),
            ],
        ], [
            'cancellation_reason.required' => 'Vui lòng chọn lý do hủy đơn hàng.',

            'cancellation_note.required' => 'Vui lòng nhập lý do hủy đơn hàng.',
        ]);

        $order = Order::with([
            'details.variant',
            'coupon',
            'freeshipCoupon',
        ])
            ->where(
                'user_id',
                Auth::id()
            )
            ->findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | Chỉ cho khách hủy khi đơn chưa giao
        |--------------------------------------------------------------------------
        */
        if (! in_array(
            $order->order_status,
            [
                'pending',
                'confirmed',
                'processing',
            ],
            true
        )) {
            return back()->with(
                'error',
                'Không thể hủy đơn hàng này do đơn hàng đang được xử lý hoặc đã giao.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Lưu trạng thái trước khi hủy
        |--------------------------------------------------------------------------
        |
        | Cần lưu để xử lý refund.
        |
        */
        $wasPaid =
            $order->payment_status === 'paid';

        $paymentMethod =
            $order->payment_method;

        /*
        |--------------------------------------------------------------------------
        | Hủy đơn + xử lý tồn kho
        |--------------------------------------------------------------------------
        |
        | OrderInventoryService chịu trách nhiệm:
        |
        | - chuyển order_status thành cancelled
        | - nhả / hoàn số lượng tồn kho nếu cần
        | - tránh hoàn stock nhiều lần
        |
        */
        try {
            $order = $inventoryService->cancel(
                $order,
                [
                    'cancelled_by' => 'customer',

                    'cancellation_reason' => $validated[
                            'cancellation_reason'
                        ],

                    'cancellation_note' => $validated[
                            'cancellation_reason'
                        ] === 'other'
                            ? trim(
                                $validated[
                                    'cancellation_note'
                                ]
                            )
                            : null,

                    'cancelled_at' => now(),
                ]
            );
        } catch (DomainException $e) {
            return back()->with(
                'error',
                $e->getMessage()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Hoàn tiền
        |--------------------------------------------------------------------------
        |
        | Nếu đơn đã thanh toán bằng VietQR hoặc Wallet
        | thì tiền được hoàn vào Ví MaxBall.
        |
        */
        if (
            $wasPaid
            && in_array(
                $paymentMethod,
                [
                    'vietqr',
                    'wallet',
                ],
                true
            )
        ) {
            $user = Auth::user();

            $user->increment(
                'wallet_balance',
                $order->total_amount
            );

            WalletTransaction::create([
                'user_id' => $user->id,

                'type' => 'refund',

                'amount' => $order->total_amount,

                'description' => 'Hoàn tiền do khách hàng tự hủy đơn hàng #'
                    .$order->order_code,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Gửi thông báo
        |--------------------------------------------------------------------------
        */
        $notifier->send($order);

        return back()->with(
            'success',
            'Đã hủy đơn hàng thành công! Lượt dùng voucher (nếu có) đã được hoàn lại; thời hạn voucher không thay đổi.'
        );
    }

    public function confirmReceipt($id)
    {
        $order = Order::where(
            'user_id',
            Auth::id()
        )
            ->findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | Chỉ xác nhận nhận hàng khi đang giao
        |--------------------------------------------------------------------------
        */
        if ($order->order_status !== 'shipping') {
            return back()->with(
                'error',
                'Không thể xác nhận đơn hàng này.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Hoàn thành đơn
        |--------------------------------------------------------------------------
        |
        | Với COD:
        | khách xác nhận đã nhận hàng → coi như đã thanh toán.
        |
        */
        $order->update([
            'order_status' => 'completed',

            'payment_status' => 'paid',
        ]);

        return back()->with(
            'success',
            'Cảm ơn bạn đã xác nhận nhận hàng thành công!'
        );
    }
}
