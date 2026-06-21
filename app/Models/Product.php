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
        'base_price' => 'integer',
        'discount_price' => 'integer',
        'view_count' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function productImages()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function getImageUrlsAttribute(): array
    {
        return $this->productImages->map->url->all();
    }

    public function getThumbnailUrlAttribute(): string
    {
        if (!$this->thumbnail) {
            return 'https://images.unsplash.com/photo-1517466787929-bc90951d0974?auto=format&fit=crop&w=900&q=80';
        }

        if (Str::startsWith($this->thumbnail, ['http://', 'https://'])) {
            return $this->thumbnail;
        }

        $path = ltrim($this->thumbnail, '/');

        return asset('storage/' . $path);
    }

    public function getCategoryNameAttribute(): string
    {
        return $this->category->name ?? 'Jersey';
    }

    public function getSlugAttribute($value)
    {
        return $value ?: Str::slug($this->name);
    }

    public function getFinalPriceAttribute(): int
    {
        return $this->discount_price ?? $this->base_price;
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->final_price, 0, ',', '.') . ' đ';
    }

    public function getFormattedBasePriceAttribute(): string
    {
        return number_format($this->base_price, 0, ',', '.') . ' đ';
    }
}
