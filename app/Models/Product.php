<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'status',
        'thumbnail',
        'description',
        'slug',
        'base_price',
        'discount_price',
        'view_count',
    ];

    protected $casts = [
        'status' => 'boolean',
        'base_price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'view_count' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getThumbnailUrlAttribute(): string
    {
        if (!$this->thumbnail) {
            return 'https://images.unsplash.com/photo-1517466787929-bc90951d0974?auto=format&fit=crop&w=900&q=80';
        }

        if (Str::startsWith($this->thumbnail, ['http://', 'https://'])) {
            return $this->thumbnail;
        }

        return asset('storage/' . ltrim($this->thumbnail, '/'));
    }

    public function getCategoryNameAttribute(): string
    {
        return $this->category->name ?? 'Jersey';
    }

    public function getSlugAttribute($value)
    {
        return $value ?: Str::slug($this->name);
    }
}
