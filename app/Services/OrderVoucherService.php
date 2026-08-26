<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\UserVoucher;

class OrderVoucherService
{
    public function restoreForCancelledOrder(Order $order): void
    {
        $couponIds = collect([
            $order->coupon_id,
            $order->freeship_coupon_id,
        ])->filter()->unique();

        foreach ($couponIds as $couponId) {
            $userVoucher = UserVoucher::query()
                ->where('user_id', $order->user_id)
                ->where('coupon_id', $couponId)
                ->lockForUpdate()
                ->first();

            if (! $userVoucher?->is_used) {
                continue;
            }

            $userVoucher->update([
                'is_used' => false,
                'used_at' => null,
            ]);

            $coupon = Coupon::query()
                ->lockForUpdate()
                ->find($couponId);

            if ($coupon && $coupon->used_count > 0) {
                $coupon->decrement('used_count');
            }
        }
    }
}
