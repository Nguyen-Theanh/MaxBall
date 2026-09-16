<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    /**
     * Tạo yêu cầu nạp tiền
     */
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:10000',
        ], [
            'amount.required' => 'Vui lòng nhập số tiền cần nạp.',
            'amount.integer' => 'Số tiền phải là số nguyên.',
            'amount.min' => 'Số tiền nạp tối thiểu là 10.000đ.',
        ]);

        $referenceCode = 'NAP' . strtoupper(Str::random(7));

        WalletTransaction::create([
            'user_id' => Auth::id(),
            'type' => 'deposit',
            'status' => 'pending',
            'reference_code' => $referenceCode,
            'amount' => $request->amount,
            'description' => 'Nạp tiền vào ví',
        ]);

        return redirect()->route('client.wallet.deposit_qr', ['reference_code' => $referenceCode]);
    }

    /**
     * Hiển thị QR nạp tiền
     */
    public function depositQr($reference_code)
    {
        $transaction = WalletTransaction::where('reference_code', $reference_code)
            ->where('user_id', Auth::id())
            ->where('type', 'deposit')
            ->firstOrFail();

        if ($transaction->status === 'completed') {
            return redirect(route('account.show') . '#wallet')->with('success', 'Nạp tiền thành công!');
        }

        $bankId = 'MB'; // MB Bank
        $accountNo = '4007052006';
        $accountName = 'NGUYEN GIA TUAN';
        $amount = $transaction->amount;
        $addInfo = $transaction->reference_code; 

        $qrUrl = "https://img.vietqr.io/image/{$bankId}-{$accountNo}-compact2.png?amount={$amount}&addInfo={$addInfo}&accountName=" . urlencode($accountName);

        return view('client.account.deposit_qr', compact('transaction', 'qrUrl', 'accountNo', 'accountName', 'bankId'));
    }

    /**
     * AJAX endpoint để kiểm tra trạng thái nạp tiền
     */
    public function checkDepositStatus($reference_code)
    {
        $transaction = WalletTransaction::where('reference_code', $reference_code)
            ->where('user_id', Auth::id())
            ->first();

        if ($transaction && $transaction->status === 'completed') {
            return response()->json(['paid' => true]);
        }

        return response()->json(['paid' => false]);
    }

    /**
     * Xử lý yêu cầu rút tiền
     */
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:50000',
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'account_name' => 'required|string|max:100',
        ], [
            'amount.required' => 'Vui lòng nhập số tiền cần rút.',
            'amount.integer' => 'Số tiền phải là số nguyên.',
            'amount.min' => 'Số tiền rút tối thiểu là 50.000đ.',
            'bank_name.required' => 'Vui lòng nhập tên ngân hàng.',
            'account_number.required' => 'Vui lòng nhập số tài khoản.',
            'account_name.required' => 'Vui lòng nhập tên chủ tài khoản.',
        ]);

        $user = Auth::user();

        // Sử dụng database transaction và lockForUpdate để chống race condition
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $user) {
                // Khóa dòng user hiện tại để kiểm tra số dư an toàn
                $lockedUser = \App\Models\User::where('id', $user->id)->lockForUpdate()->first();

                if ($lockedUser->wallet_balance < $request->amount) {
                    throw new \Exception('Số dư ví không đủ để thực hiện giao dịch này.');
                }

                // Trừ tiền trong ví
                $lockedUser->decrement('wallet_balance', $request->amount);

                // Tạo yêu cầu rút tiền
                \App\Models\WithdrawRequest::create([
                    'user_id' => $lockedUser->id,
                    'amount' => $request->amount,
                    'bank_name' => $request->bank_name,
                    'account_number' => $request->account_number,
                    'account_name' => mb_strtoupper($request->account_name),
                    'status' => 'pending',
                ]);

                // Ghi nhận lịch sử ví
                WalletTransaction::create([
                    'user_id' => $lockedUser->id,
                    'type' => 'withdraw',
                    'status' => 'pending',
                    'amount' => $request->amount,
                    'description' => 'Yêu cầu rút tiền về ngân hàng ' . $request->bank_name,
                ]);
            });

            return redirect(route('account.show') . '#wallet')->with('success', 'Đã gửi yêu cầu rút tiền. Vui lòng chờ quản trị viên duyệt.');
        } catch (\Exception $e) {
            return redirect(route('account.show') . '#wallet')->with('error', $e->getMessage());
        }
    }
}
