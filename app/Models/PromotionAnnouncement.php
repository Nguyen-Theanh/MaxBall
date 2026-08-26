<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionAnnouncement extends Model
{
    protected $fillable = [
        'title',
        'content',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
