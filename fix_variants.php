<?php

use App\Models\Product;
use Illuminate\Support\Str;

$products = Product::doesntHave('variants')->get();
foreach($products as $product) {
    $product->variants()->create([
        'name' => 'Mặc định',
        'sku' => Str::slug($product->slug ?: $product->name),
        'base_price' => $product->base_price,
        'discount_price' => $product->discount_price,
        'stock' => 0,
    ]);
}
echo "Fixed " . $products->count() . " products.\n";
