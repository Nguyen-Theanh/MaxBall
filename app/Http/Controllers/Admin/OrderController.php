<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderCancellationNotifier;
use App\Support\OrderCancellationReasons;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'processing', 'shipping', 'completed', 'cancelled'])],
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', Rule::in([10, 20, 50])],
        ]);
        $perPage = (int) ($validated['per_page'] ?? 10);
        $query = Order::with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if (! empty($validated['status'])) {
            $query->where('order_status', $validated['status']);
        }

        if (! empty($validated['search'])) {
            $search = trim($validated['search']);
            $query->where(function ($query) use ($search): void {
                $query->where('order_code', 'like', '%'.$search.'%')
                    ->orWhere('customer_name', 'like', '%'.$search.'%')
                    ->orWhere('customer_phone', 'like', '%'.$search.'%');
            });
        }

        $orders = $query->paginate($perPage)->withQueryString();
        $adminCancellationReasons = OrderCancellationReasons::admin();

        return view('admin.orders.index', compact('orders', 'adminCancellationReasons'));
    }

    public function show(Order $order)
    {
        $order->load('details.variant.product', 'user');
        $adminCancellationReasons = OrderCancellationReasons::admin();

        return view('admin.orders.show', compact('order', 'adminCancellationReasons'));
    }

    public function updateStatus(Request $request, Order $order, OrderCancellationNotifier $notifier)
    {
        $rules = [
            'order_status' => ['required', Rule::in(['pending', 'processing', 'shipping', 'completed', 'cancelled'])],
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
                Rule::requiredIf($request->input('cancellation_reason') === 'other'),
            ];
        }

        $validated = $request->validate($rules, [
            'cancellation_reason.required' => 'Vui lòng chọn lý do hủy đơn hàng.',
            'cancellation_note.required' => 'Vui lòng nhập ghi chú cho lý do hủy đơn hàng.',
        ]);

        $currentStatus = $order->order_status;
        $newStatus = $request->order_status;

        // Terminal states cannot be changed
        if (in_array($currentStatus, ['completed', 'cancelled'])) {
            return back()->with('error', 'Không thể thay đổi trạng thái của đơn hàng đã Hoàn thành hoặc Đã hủy.');
        }

        // Validate sequence
        $validTransitions = [
            'pending' => ['processing', 'cancelled'],
            'processing' => ['shipping', 'cancelled'],
            'shipping' => ['completed', 'cancelled'],
        ];

        // Prevent completion if not paid
        if ($newStatus === 'completed' && $order->payment_status !== 'paid') {
            return back()->with('error', 'Đơn hàng phải được thanh toán trước khi hoàn thành.');
        }

        if (! in_array($newStatus, $validTransitions[$currentStatus] ?? [])) {
            return back()->with('error', 'Trạng thái chuyển đổi không hợp lệ.');
        }

        // Handle stock deduction when COD order is confirmed
        if ($newStatus === 'processing' && $currentStatus === 'pending' && $order->payment_method === 'cod') {
            // Kiểm tra số lượng tồn kho trước khi xác nhận
            foreach ($order->details as $detail) {
                if ($detail->variant && $detail->variant->stock < $detail->quantity) {
                    return back()->with('error', "Không thể xác nhận đơn hàng. Sản phẩm '{$detail->variant->product->name} - {$detail->variant->name}' hiện chỉ còn {$detail->variant->stock} trong kho (yêu cầu: {$detail->quantity}).");
                }
            }

            foreach ($order->details as $detail) {
                if ($detail->variant) {
                    $detail->variant->decrement('stock', $detail->quantity);
                }
            }
        }

        // Handle stock restoration when cancelling
        if ($newStatus === 'cancelled') {
            $stockWasDeducted = false;
            if ($order->payment_method === 'cod' && in_array($currentStatus, ['processing', 'shipping', 'completed'])) {
                $stockWasDeducted = true;
            } elseif ($order->payment_method === 'vietqr' && $order->payment_status === 'paid') {
                $stockWasDeducted = true;
            }

            if ($stockWasDeducted) {
                foreach ($order->details as $detail) {
                    if ($detail->variant) {
                        $detail->variant->increment('stock', $detail->quantity);
                    }
                }
            }
        }

        $updateData = [
            'order_status' => $newStatus,
        ];

        if ($newStatus === 'cancelled') {
            $updateData += [
                'cancelled_by' => 'admin',
                'cancellation_reason' => $validated['cancellation_reason'],
                'cancellation_note' => $validated['cancellation_reason'] === 'other'
                    ? trim($validated['cancellation_note'])
                    : null,
                'cancelled_at' => now(),
            ];

            // Refund logic
            if ($order->payment_status === 'paid' && in_array($order->payment_method, ['vietqr', 'wallet'])) {
                $user = $order->user;
                if ($user) {
                    $user->increment('wallet_balance', $order->total_amount);
                    \App\Models\WalletTransaction::create([
                        'user_id' => $user->id,
                        'type' => 'refund',
                        'amount' => $order->total_amount,
                        'description' => 'Hoàn tiền do Admin hủy đơn hàng #' . $order->order_code,
                    ]);
                }
            }

            // Refund Vouchers
            if ($order->coupon_id) {
                $userVoucher = \App\Models\UserVoucher::where('user_id', $order->user_id)->where('coupon_id', $order->coupon_id)->first();
                if ($userVoucher) {
                    $userVoucher->update(['is_used' => false, 'used_at' => null]);
                    if ($order->coupon) {
                        $order->coupon->decrement('used_count');
                    }
                }
            }

            if ($order->freeship_coupon_id) {
                $userVoucherFreeship = \App\Models\UserVoucher::where('user_id', $order->user_id)->where('coupon_id', $order->freeship_coupon_id)->first();
                if ($userVoucherFreeship) {
                    $userVoucherFreeship->update(['is_used' => false, 'used_at' => null]);
                    if ($order->freeshipCoupon) {
                        $order->freeshipCoupon->decrement('used_count');
                    }
                }
            }
        }

        $order->update($updateData);

        if ($newStatus === 'cancelled') {
            $notifier->send($order);
        }

        return back()->with('success', 'Đã cập nhật trạng thái đơn hàng thành công.');
    }

    public function updatePaymentStatus(Request $request, Order $order)
    {
        $request->validate([
            'payment_status' => 'required|in:paid,failed,pending',
        ]);

        if ($order->payment_status === 'paid') {
            return back()->with('error', 'Đơn hàng này đã được thanh toán trước đó.');
        }

        $order->update([
            'payment_status' => $request->payment_status,
        ]);

        return back()->with('success', 'Đã cập nhật trạng thái thanh toán thành công.');
    }
}
