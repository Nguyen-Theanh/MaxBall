<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderCancellationNotifier;
use App\Support\OrderCancellationReasons;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['details.variant.product', 'details.review'])
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(8)
            ->withQueryString()
            ->fragment('orders');

        $customerCancellationReasons = OrderCancellationReasons::customer();

        return view('client.orders.index', compact('orders', 'customerCancellationReasons'));
    }

    public function show($id)
    {
        $order = Order::with(['details.variant.product', 'details.review'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        $customerCancellationReasons = OrderCancellationReasons::customer();

        return view('client.orders.show', compact('order', 'customerCancellationReasons'));
    }

    public function cancel(Request $request, OrderCancellationNotifier $notifier, $id)
    {
        $validated = $request->validate([
            'cancellation_reason' => [
                'required',
                Rule::in(array_keys(OrderCancellationReasons::customer())),
            ],
            'cancellation_note' => [
                'nullable',
                'string',
                'max:1000',
                Rule::requiredIf($request->input('cancellation_reason') === 'other'),
            ],
        ], [
            'cancellation_reason.required' => 'Vui lòng chọn lý do hủy đơn hàng.',
            'cancellation_note.required' => 'Vui lòng nhập lý do hủy đơn hàng.',
        ]);

        $order = Order::where('user_id', Auth::id())->findOrFail($id);

        if (! in_array($order->order_status, ['pending', 'processing'], true)) {
            return back()->with('error', 'Không thể hủy đơn hàng này do đơn hàng đang được xử lý hoặc đã giao.');
        }

        $order->update([
            'order_status' => 'cancelled',
            'cancelled_by' => 'customer',
            'cancellation_reason' => $validated['cancellation_reason'],
            'cancellation_note' => $validated['cancellation_reason'] === 'other'
                ? trim($validated['cancellation_note'])
                : null,
            'cancelled_at' => now(),
        ]);

        // Restore stock
        $stockWasDeducted = false;
        if ($order->payment_method === 'cod' && $order->order_status === 'processing') {
            $stockWasDeducted = true;
        } elseif (in_array($order->payment_method, ['vietqr', 'wallet']) && $order->payment_status === 'paid') {
            $stockWasDeducted = true;
        }

        if ($stockWasDeducted) {
            foreach ($order->details as $detail) {
                if ($detail->variant) {
                    $detail->variant->increment('stock', $detail->quantity);
                }
            }
        }

        // Refund logic
        if ($order->payment_status === 'paid' && in_array($order->payment_method, ['vietqr', 'wallet'])) {
            $user = Auth::user();
            $user->increment('wallet_balance', $order->total_amount);
            \App\Models\WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'refund',
                'amount' => $order->total_amount,
                'description' => 'Hoàn tiền do khách hàng tự hủy đơn hàng #' . $order->order_code,
            ]);
        }

        // Refund discount voucher
        if ($order->coupon_id) {
            $userVoucher = \App\Models\UserVoucher::where('user_id', $order->user_id)->where('coupon_id', $order->coupon_id)->first();
            if ($userVoucher) {
                $userVoucher->update(['is_used' => false, 'used_at' => null]);
                if ($order->coupon) {
                    $order->coupon->decrement('used_count');
                }
            }
        }

        // Refund freeship voucher
        if ($order->freeship_coupon_id) {
            $userVoucherFreeship = \App\Models\UserVoucher::where('user_id', $order->user_id)->where('coupon_id', $order->freeship_coupon_id)->first();
            if ($userVoucherFreeship) {
                $userVoucherFreeship->update(['is_used' => false, 'used_at' => null]);
                if ($order->freeshipCoupon) {
                    $order->freeshipCoupon->decrement('used_count');
                }
            }
        }

        $notifier->send($order);

        return back()->with('success', 'Đã hủy đơn hàng thành công!');
    }

    public function confirmReceipt($id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);

        if ($order->order_status !== 'shipping') {
            return back()->with('error', 'Không thể xác nhận đơn hàng này.');
        }

        $order->update([
            'order_status' => 'completed',
            'payment_status' => 'paid',
        ]);

        return back()->with('success', 'Cảm ơn bạn đã xác nhận nhận hàng thành công!');
    }
}
