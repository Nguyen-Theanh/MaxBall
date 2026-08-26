<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'coupon_id',
        'is_used',
        'used_at',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'used_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function getIsAvailableAttribute(): bool
    {
        $coupon = $this->coupon;

        return ! $this->is_used
            && $coupon
            && $coupon->status
            && (! $coupon->start_date || $coupon->start_date->lte(now()))
            && (! $coupon->expires_at || $coupon->expires_at->gte(now()))
            && ($coupon->discount_type !== 'percent' || $coupon->max_discount_amount > 0)
            && (! $coupon->usage_limit || $coupon->used_count < $coupon->usage_limit);
    }

    public function getStatusLabelAttribute(): string
    {
        $coupon = $this->coupon;

        if ($this->is_used) {
            return 'Đã sử dụng';
        }

        if (! $coupon || ! $coupon->status) {
            return 'Ngừng áp dụng';
        }

        if ($coupon->start_date?->isFuture()) {
            return 'Chưa bắt đầu';
        }

        if ($coupon->expires_at?->isPast()) {
            return 'Đã hết hạn';
        }

        if ($coupon->discount_type === 'percent' && ! $coupon->max_discount_amount) {
            return 'Chưa khả dụng';
        }

        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return 'Đã hết lượt';
        }

        return 'Có thể sử dụng';
    }
}
