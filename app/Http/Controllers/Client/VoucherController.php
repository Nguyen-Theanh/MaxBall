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
            ->where(function ($query) use ($user) {
                $query->where('is_public', true);

                if ($user) {
                    $query->orWhereHas('userVouchers', function ($voucherQuery) use ($user) {
                        $voucherQuery->where('user_id', $user->id);
                    });
                }
            })
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->where(function ($q) {
                $q->where('discount_type', '!=', 'percent')
                    ->orWhere('max_discount_amount', '>', 0);
            })
            ->get();

        $savedVouchers = $user
            ? $user->userVouchers()->get(['coupon_id', 'is_used'])->keyBy('coupon_id')
            : collect();

        $data = $vouchers->map(function ($coupon) use ($savedVouchers) {
            $savedVoucher = $savedVouchers->get($coupon->id);
            $isUsed = (bool) $savedVoucher?->is_used;

            return [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'description' => $coupon->description,
                'discount_type' => $coupon->discount_type,
                'discount_value' => $coupon->discount_value,
                'max_discount_amount' => $coupon->max_discount_amount,
                'min_order_value' => $coupon->min_order_value,
                'expires_at' => $coupon->expires_at ? $coupon->expires_at->format('d/m/Y') : 'Không giới hạn',
                'is_saved' => $savedVoucher !== null,
                'is_used' => $isUsed,
                'is_exhausted' => $coupon->is_exhausted,
                'is_available' => ! $isUsed && ! $coupon->is_exhausted,
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
            'coupon_id' => 'required|exists:coupons,id',
        ]);

        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập để lưu mã giảm giá.']);
        }

        $coupon = Coupon::find($request->coupon_id);

        if (! $coupon->status ||
            ($coupon->expires_at && $coupon->expires_at < now()) ||
            ($coupon->start_date && $coupon->start_date > now()) ||
            ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit)) {
            return response()->json(['success' => false, 'message' => 'Mã giảm giá này đã hết hạn hoặc hết số lượng.']);
        }

        $existingVoucher = UserVoucher::where('user_id', $user->id)
            ->where('coupon_id', $coupon->id)
            ->first();

        if (! $coupon->is_public && ! $existingVoucher) {
            return response()->json(['success' => false, 'message' => 'Voucher này chỉ dành cho tài khoản được tặng.']);
        }

        if ($existingVoucher) {
            return response()->json(['success' => false, 'message' => 'Bạn đã lưu mã giảm giá này rồi.']);
        }

        UserVoucher::create([
            'user_id' => $user->id,
            'coupon_id' => $coupon->id,
            'is_used' => false,
        ]);

        return response()->json(['success' => true, 'message' => 'Lưu mã giảm giá thành công!']);
    }

    // API to validate and get voucher details at checkout
    public function validateVoucher(Request $request)
    {
        $code = $request->input('code');
        $user = Auth::user();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập.']);
        }

        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon) {
            return response()->json(['success' => false, 'message' => 'Mã giảm giá không tồn tại.']);
        }

        if (! $coupon->status ||
            ($coupon->expires_at && $coupon->expires_at < now()) ||
            ($coupon->start_date && $coupon->start_date > now()) ||
            ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit)) {
            return response()->json(['success' => false, 'message' => 'Mã giảm giá không khả dụng (đã hết hạn hoặc hết lượt dùng).']);
        }

        if ($coupon->discount_type === 'percent' && ! $coupon->max_discount_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher phần trăm chưa được cấu hình số tiền giảm tối đa.',
            ]);
        }

        $userVoucher = UserVoucher::where('user_id', $user->id)->where('coupon_id', $coupon->id)->first();

        if (! $coupon->is_public && ! $userVoucher) {
            return response()->json(['success' => false, 'message' => 'Mã giảm giá không khả dụng.']);
        }

        // Check if user has already USED it
        if ($userVoucher && $userVoucher->is_used) {
            return response()->json(['success' => false, 'message' => 'Bạn đã sử dụng mã giảm giá này rồi.']);
        }

        // Auto save if not saved yet (as requested by user)
        if (! $userVoucher) {
            UserVoucher::create([
                'user_id' => $user->id,
                'coupon_id' => $coupon->id,
                'is_used' => false,
            ]);
        }

        return response()->json([
            'success' => true,
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'discount_type' => $coupon->discount_type,
                'discount_value' => $coupon->discount_value,
                'max_discount_amount' => $coupon->max_discount_amount,
                'min_order_value' => $coupon->min_order_value,
            ],
        ]);
    }
}
