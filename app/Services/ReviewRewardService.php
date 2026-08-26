<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Str;

class ReviewRewardService
{
    public function awardFreeshipVoucher(User $user, Review $review): Coupon
    {
        $coupon = Coupon::create([
            'code' => sprintf(
                'REVIEW-%d-%s',
                $review->id,
                Str::upper(Str::random(8))
            ),
            'description' => 'Quà tặng freeship sau khi đánh giá sản phẩm',
            'discount_type' => 'freeship',
            'discount_value' => 0,
            'min_order_value' => 0,
            'usage_limit' => 1,
            'used_count' => 0,
            'start_date' => null,
            'expires_at' => null,
            'status' => true,
            'is_public' => false,
        ]);

        $coupon->userVouchers()->create([
            'user_id' => $user->id,
            'is_used' => false,
        ]);

        return $coupon;
    }
}
