<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'receiver_name',
        'receiver_phone',
        'address_line',
        'province_code',
        'province_name',
        'ward_code',
        'ward_name',
        'address_detail',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'province_code' => 'integer',
            'ward_code' => 'integer',
            'is_default' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
