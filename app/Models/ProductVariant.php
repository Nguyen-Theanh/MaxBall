<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'base_price',
        'discount_price',
        'stock',
        'image_url',
    ];

    protected $casts = [
        'base_price' => 'integer',
        'discount_price' => 'integer',
        'stock' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function getVariantImageUrlAttribute(): ?string
    {
        if (!$this->image_url) {
            return null;
        }

        if (Str::startsWith($this->image_url, ['http://', 'https://'])) {
            return $this->image_url;
        }

        return asset('storage/' . ltrim($this->image_url, '/'));
    }
}
