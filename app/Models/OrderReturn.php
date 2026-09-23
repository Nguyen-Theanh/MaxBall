<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderReturn extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'type',
        'reason',
        'description',
        'images',
        'status',
        'refund_amount',
        'admin_note',
    ];

    protected $casts = [
        'images' => 'array',
        'refund_amount' => 'decimal:2',
    ];

    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Chờ xử lý',
            'processing' => 'Đang xử lý',
            'resolved' => 'Đã giải quyết',
            'rejected' => 'Từ chối',
            default => 'Không xác định',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'return' ? 'Trả hàng/Hoàn tiền' : 'Khiếu nại';
    }
}
