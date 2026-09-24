<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderReturn;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = OrderReturn::with(['order', 'user'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $returns = $query->paginate(15)->withQueryString();

        return view('admin.returns.index', compact('returns'));
    }

    public function show($id)
    {
        $orderReturn = OrderReturn::with(['order.details.variant.product', 'user'])->findOrFail($id);
        
        return view('admin.returns.show', compact('orderReturn'));
    }

    public function updateStatus(Request $request, $id)
    {
        $orderReturn = OrderReturn::with('order', 'user')->findOrFail($id);

        if (in_array($orderReturn->status, ['resolved', 'rejected'])) {
            return back()->with('error', 'Yêu cầu này đã được xử lý xong.');
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['processing', 'resolved', 'rejected'])],
            'admin_note' => ['nullable', 'string', 'max:1000'],
            'refund_amount' => ['nullable', 'numeric', 'min:0', 'max:' . $orderReturn->order->total_amount],
        ]);

        DB::transaction(function () use ($orderReturn, $validated) {
            $orderReturn->status = $validated['status'];
            $orderReturn->admin_note = $validated['admin_note'] ?? $orderReturn->admin_note;
            
            if ($validated['status'] === 'resolved' && !empty($validated['refund_amount']) && $validated['refund_amount'] > 0 && !$orderReturn->refund_amount) {
                $orderReturn->refund_amount = $validated['refund_amount'];
                
                // Refund to Wallet
                $user = $orderReturn->user;
                $user->increment('wallet_balance', $validated['refund_amount']);
                
                WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'refund',
                    'amount' => $validated['refund_amount'],
                    'description' => ($orderReturn->type === 'return' ? 'Hoàn tiền trả hàng' : 'Đền bù khiếu nại') . ' cho đơn hàng #' . $orderReturn->order->order_code,
                    'status' => 'completed'
                ]);
            }
            
            $orderReturn->save();
        });

        return back()->with('success', 'Đã cập nhật trạng thái yêu cầu thành công.');
    }
}
