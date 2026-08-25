<?php

namespace App\Models;

use App\Support\OrderCancellationReasons;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'coupon_id',
        'freeship_coupon_id',
        'order_code',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'sub_total',
        'shipping_fee',
        'discount_amount',
        'total_amount',
        'payment_method',
        'payment_status',
        'order_status',
        'cancelled_by',
        'cancellation_reason',
        'cancellation_note',
        'cancelled_at',
    ];

    protected $casts = [
        'sub_total' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'cancelled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function review()
    {
        return $this->hasOneThrough(Review::class, OrderDetail::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function freeshipCoupon()
    {
        return $this->belongsTo(Coupon::class, 'freeship_coupon_id');
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function getCancellationReasonLabelAttribute(): ?string
    {
        return OrderCancellationReasons::label(
            $this->cancellation_reason,
            $this->cancelled_by
        );
    }
}
