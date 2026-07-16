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
            'order_status' => 'required|in:pending,processing,shipping,completed,cancelled'
        ]);

        $currentStatus = $order->order_status;
        $newStatus = $request->order_status;

        // Terminal states cannot be changed
        if (in_array($currentStatus, ['completed', 'cancelled'])) {
            return back()->with('error', 'Không thể thay đổi trạng thái của đơn hàng đã Hoàn thành hoặc Đã hủy.');
        }

        // Validate sequence
        $validTransitions = [
            'pending' => ['processing', 'shipping', 'cancelled'],
            'processing' => ['shipping', 'cancelled'],
            'shipping' => ['completed', 'cancelled'],
        ];

        // Prevent completion if not paid
        if ($newStatus === 'completed' && $order->payment_status !== 'paid') {
            return back()->with('error', 'Đơn hàng phải được thanh toán trước khi hoàn thành.');
        }

        if (!in_array($newStatus, $validTransitions[$currentStatus] ?? [])) {
            return back()->with('error', 'Trạng thái chuyển đổi không hợp lệ.');
        }

        // If cancelling, restore stock
        if ($newStatus === 'cancelled') {
            foreach ($order->details as $detail) {
                if ($detail->variant) {
                    $detail->variant->increment('stock', $detail->quantity);
                }
            }
        } 

        $order->update([
            'order_status' => $newStatus,
        ]);

        return back()->with('success', 'Đã cập nhật trạng thái đơn hàng thành công.');
    }

    public function updatePaymentStatus(Request $request, Order $order)
    {
        $request->validate([
            'payment_status' => 'required|in:paid,failed,pending'
        ]);

        if ($order->payment_status === 'paid') {
            return back()->with('error', 'Đơn hàng này đã được thanh toán trước đó.');
        }

        $order->update([
            'payment_status' => $request->payment_status
        ]);

        return back()->with('success', 'Đã cập nhật trạng thái thanh toán thành công.');
    }
}
