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
        'reserved_stock',
        'image_url',
    ];

    protected $casts = [
        'base_price' => 'integer',
        'discount_price' => 'integer',
        'stock' => 'integer',
        'reserved_stock' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

<<<<<<< Updated upstream
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function getAvailableStockAttribute(): int
    {
        return max(0, (int) $this->stock - (int) $this->reserved_stock);
    }

    public function getVariantImageUrlAttribute(): ?string
    {
        if (! $this->image_url) {
            return null;
        }

        if (Str::startsWith($this->image_url, ['http://', 'https://'])) {
            return $this->image_url;
        }

        return asset('storage/'.ltrim($this->image_url, '/'));
=======
    public function attributeValues()
    {
        return $this->belongsToMany(
            ProductAttributeValue::class,
            'product_variant_attribute_value',
            'product_variant_id',
            'product_attribute_value_id'
        )->with('attribute')->withTimestamps();
>>>>>>> Stashed changes
    }
}
