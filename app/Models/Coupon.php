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

    public function freeshipOrders()
    {
        return $this->hasMany(Order::class, 'freeship_coupon_id');
    }

    public function hasOrders(): bool
    {
        return $this->orders()->exists() || $this->freeshipOrders()->exists();
    }

    public function getHasOrdersAttribute(): bool
    {
        $ordersCount = array_key_exists('orders_count', $this->attributes)
            ? (int) $this->attributes['orders_count']
            : $this->orders()->count();
        $freeshipOrdersCount = array_key_exists('freeship_orders_count', $this->attributes)
            ? (int) $this->attributes['freeship_orders_count']
            : $this->freeshipOrders()->count();

        return $ordersCount > 0 || $freeshipOrdersCount > 0;
    }

    public function getIsExhaustedAttribute(): bool
    {
        return $this->usage_limit !== null
            && $this->used_count >= $this->usage_limit;
    }

    public function getIsCurrentlyAvailableAttribute(): bool
    {
        return $this->status
            && (! $this->start_date || $this->start_date->lte(now()))
            && (! $this->expires_at || $this->expires_at->gte(now()))
            && ! $this->is_exhausted
            && ($this->discount_type !== 'percent' || $this->max_discount_amount > 0);
    }

    public function getAvailabilityLabelAttribute(): string
    {
        if (! $this->status) {
            return 'Đã tắt';
        }

        if ($this->start_date?->isFuture()) {
            return 'Chưa bắt đầu';
        }

        if ($this->expires_at?->isPast()) {
            return 'Đã hết hạn';
        }

        if ($this->is_exhausted) {
            return 'Đã hết lượt';
        }

        if ($this->discount_type === 'percent' && ! $this->max_discount_amount) {
            return 'Chưa cấu hình đủ';
        }

        return 'Đang hoạt động';
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

    public function scopeNotCurrentlyAvailable(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query->where('status', false)
                ->orWhereNull('status')
                ->orWhere('start_date', '>', now())
                ->orWhere('expires_at', '<', now())
                ->orWhere(function (Builder $query) {
                    $query->whereNotNull('usage_limit')
                        ->whereColumn('used_count', '>=', 'usage_limit');
                })
                ->orWhere(function (Builder $query) {
                    $query->where('discount_type', 'percent')
                        ->where(function (Builder $query) {
                            $query->whereNull('max_discount_amount')
                                ->orWhere('max_discount_amount', '<=', 0);
                        });
                });
        });
    }
}
