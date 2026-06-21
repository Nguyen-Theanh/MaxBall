<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/', function () {
    return redirect()->route('client.products.index');
});

Route::get('/products', function () {
    $products = DB::table('products')
        ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
        ->where('products.status', true)
        ->select('products.*', 'categories.name as category_name')
        ->orderByDesc('products.updated_at')
        ->orderByDesc('products.id')
        ->get()
        ->map(function ($product) {
            $thumbnail = $product->thumbnail;

            if ($thumbnail && ! Str::startsWith($thumbnail, ['http://', 'https://'])) {
                $thumbnail = asset('storage/' . ltrim($thumbnail, '/'));
            }

            return [
                'name' => $product->name,
                'slug' => $product->slug ?: Str::slug($product->name),
                'thumbnail' => $thumbnail,
                'base_price' => (float) $product->base_price,
                'discount_price' => $product->discount_price ? (float) $product->discount_price : null,
                'description' => $product->description,
                'category_name' => $product->category_name,
            ];
        });

    return view('client.products.index', [
        'products' => $products,
    ]);
})->name('client.products.index');
