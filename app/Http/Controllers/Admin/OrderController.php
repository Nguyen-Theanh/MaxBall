<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('order_status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('order_code', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_name', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_phone', 'like', '%' . $request->search . '%');
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('details.variant.product', 'user');
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'order_status' => 'required|in:pending,shipping,completed,cancelled',
            'payment_status' => 'required|in:pending,paid,failed'
        ]);

        // If cancelling, restore stock
        if ($request->order_status === 'cancelled' && $order->order_status !== 'cancelled') {
            foreach ($order->details as $detail) {
                $detail->variant->increment('stock', $detail->quantity);
            }
        } 
        // If un-cancelling, deduct stock (optional, but good for completeness, assuming we allow it)
        elseif ($order->order_status === 'cancelled' && $request->order_status !== 'cancelled') {
            foreach ($order->details as $detail) {
                $detail->variant->decrement('stock', $detail->quantity);
            }
        }

        $order->update([
            'order_status' => $request->order_status,
            'payment_status' => $request->payment_status,
        ]);

        return back()->with('success', 'Đã cập nhật trạng thái đơn hàng thành công.');
    }
}
