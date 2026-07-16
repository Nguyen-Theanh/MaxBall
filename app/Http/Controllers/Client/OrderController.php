<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('details.variant.product')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('client.orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with('details.variant.product')
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('client.orders.show', compact('order'));
    }

    public function cancel($id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);

        if ($order->order_status !== 'pending') {
            return back()->with('error', 'Không thể hủy đơn hàng này do đơn hàng đang được xử lý hoặc đã giao.');
        }

        $order->update(['order_status' => 'cancelled']);

        // Restore stock
        foreach ($order->details as $detail) {
            $detail->variant->increment('stock', $detail->quantity);
        }

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
            'payment_status' => 'paid'
        ]);

        return back()->with('success', 'Cảm ơn bạn đã xác nhận nhận hàng thành công!');
    }
}
