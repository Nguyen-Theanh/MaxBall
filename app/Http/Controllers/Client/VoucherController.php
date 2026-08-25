<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\UserVoucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VoucherController extends Controller
{
    // API for listing active vouchers
    public function getActiveVouchers()
    {
        $user = Auth::user();
        
        $vouchers = Coupon::where('status', true)
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('usage_limit')->orWhereRaw('used_count < usage_limit');
            })
            ->get();

        $savedVoucherIds = $user ? $user->userVouchers()->pluck('coupon_id')->toArray() : [];

        $data = $vouchers->map(function ($coupon) use ($savedVoucherIds) {
            return [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'description' => $coupon->description,
                'discount_type' => $coupon->discount_type,
                'discount_value' => $coupon->discount_value,
                'min_order_value' => $coupon->min_order_value,
                'expires_at' => $coupon->expires_at ? $coupon->expires_at->format('d/m/Y') : 'Không giới hạn',
                'is_saved' => in_array($coupon->id, $savedVoucherIds),
            ];
        });

        $data = $data->sortByDesc(function ($v) {
            return $v['discount_type'] === 'freeship' ? 1 : 0;
        })->values();

        return response()->json(['vouchers' => $data]);
    }

    // API to save a voucher
    public function saveVoucher(Request $request)
    {
        $request->validate([
            'coupon_id' => 'required|exists:coupons,id'
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập để lưu mã giảm giá.']);
        }

        $coupon = Coupon::find($request->coupon_id);

        if (!$coupon->status || 
            ($coupon->expires_at && $coupon->expires_at < now()) || 
            ($coupon->start_date && $coupon->start_date > now()) ||
            ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit)) {
            return response()->json(['success' => false, 'message' => 'Mã giảm giá này đã hết hạn hoặc hết số lượng.']);
        }

        $exists = UserVoucher::where('user_id', $user->id)->where('coupon_id', $coupon->id)->exists();
        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Bạn đã lưu mã giảm giá này rồi.']);
        }

        UserVoucher::create([
            'user_id' => $user->id,
            'coupon_id' => $coupon->id,
            'is_used' => false
        ]);

        return response()->json(['success' => true, 'message' => 'Lưu mã giảm giá thành công!']);
    }

    // API to validate and get voucher details at checkout
    public function validateVoucher(Request $request)
    {
        $code = $request->input('code');
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập.']);
        }

        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'Mã giảm giá không tồn tại.']);
        }

        if (!$coupon->status || 
            ($coupon->expires_at && $coupon->expires_at < now()) || 
            ($coupon->start_date && $coupon->start_date > now()) ||
            ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit)) {
            return response()->json(['success' => false, 'message' => 'Mã giảm giá không khả dụng (đã hết hạn hoặc hết lượt dùng).']);
        }

        // Check if user has already USED it
        $userVoucher = UserVoucher::where('user_id', $user->id)->where('coupon_id', $coupon->id)->first();
        if ($userVoucher && $userVoucher->is_used) {
            return response()->json(['success' => false, 'message' => 'Bạn đã sử dụng mã giảm giá này rồi.']);
        }

        // Auto save if not saved yet (as requested by user)
        if (!$userVoucher) {
            UserVoucher::create([
                'user_id' => $user->id,
                'coupon_id' => $coupon->id,
                'is_used' => false
            ]);
        }

        return response()->json([
            'success' => true,
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'discount_type' => $coupon->discount_type,
                'discount_value' => $coupon->discount_value,
                'min_order_value' => $coupon->min_order_value,
            ]
        ]);
    }
}
