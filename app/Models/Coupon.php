<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'code',
        'description',
        'discount_type',
        'discount_value',
        'max_discount_amount',
        'min_order_value',
        'usage_limit',
        'used_count',
        'start_date',
        'expires_at',
        'status',
        'is_public',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'expires_at' => 'datetime',
        'discount_value' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'min_order_value' => 'decimal:2',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'status' => 'boolean',
        'is_public' => 'boolean',
    ];

    public function userVouchers()
    {
        return $this->hasMany(UserVoucher::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getIsExhaustedAttribute(): bool
    {
        return $this->usage_limit !== null
            && $this->used_count >= $this->usage_limit;
    }

    public function scopeCurrentlyAvailable(Builder $query): Builder
    {
        return $query
            ->where('status', true)
            ->where(function (Builder $query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function (Builder $query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->where(function (Builder $query) {
                $query->whereNull('usage_limit')
                    ->orWhereColumn('used_count', '<', 'usage_limit');
            })
            ->where(function (Builder $query) {
                $query->where('discount_type', '!=', 'percent')
                    ->orWhere('max_discount_amount', '>', 0);
            });
    }
}
