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
    public function index(Request )
    {
         = OrderReturn::with(['order', 'user'])->latest();

        if (->filled('status')) {
            ->where('status', ->status);
        }

        if (->filled('type')) {
            ->where('type', ->type);
        }

         = ->paginate(15)->withQueryString();

        return view('admin.returns.index', compact('returns'));
    }

    public function show()
    {
         = OrderReturn::with(['order.details.variant.product', 'user'])->findOrFail();
        
        return view('admin.returns.show', compact('orderReturn'));
    }

    public function updateStatus(Request , )
    {
         = OrderReturn::with('order', 'user')->findOrFail();

        if (in_array(->status, ['resolved', 'rejected'])) {
            return back()->with('error', 'Yêu cầu này đã được xử lý xong.');
        }

         = ->validate([
            'status' => ['required', Rule::in(['processing', 'resolved', 'rejected'])],
            'admin_note' => ['nullable', 'string', 'max:1000'],
            'refund_amount' => ['nullable', 'numeric', 'min:0', 'max:' . ->order->total_amount],
        ]);

        DB::transaction(function () use (, ) {
            ->status = ['status'];
            ->admin_note = ['admin_note'] ?? ->admin_note;
            
            if (['status'] === 'resolved' && !empty(['refund_amount']) && ['refund_amount'] > 0 && !->refund_amount) {
                ->refund_amount = ['refund_amount'];
                
                // Refund to Wallet
                 = ->user;
                ->increment('wallet_balance', ['refund_amount']);
                
                WalletTransaction::create([
                    'user_id' => ->id,
                    'type' => 'refund',
                    'amount' => ['refund_amount'],
                    'description' => (->type === 'return' ? 'Hoàn tiền trả hàng' : 'Đền bù khiếu nại') . ' cho đơn hàng #' . ->order->order_code,
                    'status' => 'completed'
                ]);
            }
            
            ->save();
        });

        return back()->with('success', 'Đã cập nhật trạng thái yêu cầu thành công.');
    }
}
