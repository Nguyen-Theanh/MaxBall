<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\WithdrawRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawController extends Controller
{
    /**
     * Hiển thị danh sách yêu cầu rút tiền
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');

        $query = WithdrawRequest::with('user')->orderBy('created_at', 'desc');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $withdrawRequests = $query->paginate(15);

        return view('admin.withdrawals.index', compact('withdrawRequests', 'status'));
    }

    /**
     * Duyệt yêu cầu rút tiền
     */
    public function approve(Request $request, $id)
    {
        try {
            DB::transaction(function () use ($id) {
                $withdrawRequest = WithdrawRequest::lockForUpdate()->findOrFail($id);

                if ($withdrawRequest->status !== 'pending') {
                    throw new \Exception('Yêu cầu này đã được xử lý.');
                }

                $withdrawRequest->status = 'approved';
                $withdrawRequest->save();

                // Cập nhật trạng thái transaction
                WalletTransaction::where('user_id', $withdrawRequest->user_id)
                    ->where('type', 'withdraw')
                    ->where('amount', $withdrawRequest->amount)
                    ->where('status', 'pending')
                    ->latest()
                    ->first()
                    ?->update(['status' => 'completed']);
            });

            return redirect()->back()->with('success', 'Đã duyệt yêu cầu rút tiền thành công.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Từ chối yêu cầu rút tiền
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'admin_note' => 'required|string|max:255'
        ], [
            'admin_note.required' => 'Vui lòng nhập lý do từ chối.'
        ]);

        try {
            DB::transaction(function () use ($request, $id) {
                $withdrawRequest = WithdrawRequest::lockForUpdate()->findOrFail($id);

                if ($withdrawRequest->status !== 'pending') {
                    throw new \Exception('Yêu cầu này đã được xử lý.');
                }

                $withdrawRequest->status = 'rejected';
                $withdrawRequest->admin_note = $request->admin_note;
                $withdrawRequest->save();

                // Cập nhật trạng thái transaction thành thất bại
                WalletTransaction::where('user_id', $withdrawRequest->user_id)
                    ->where('type', 'withdraw')
                    ->where('amount', $withdrawRequest->amount)
                    ->where('status', 'pending')
                    ->latest()
                    ->first()
                    ?->update(['status' => 'failed']);

                // Hoàn tiền lại cho user
                $user = User::lockForUpdate()->find($withdrawRequest->user_id);
                $user->increment('wallet_balance', $withdrawRequest->amount);

                // Ghi log hoàn tiền
                WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'refund',
                    'status' => 'completed',
                    'amount' => $withdrawRequest->amount,
                    'description' => 'Hoàn tiền do từ chối rút tiền: ' . $request->admin_note,
                ]);
            });

            return redirect()->back()->with('success', 'Đã từ chối yêu cầu và hoàn tiền cho khách.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
