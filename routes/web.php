<?php

use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Client\ProductController as ClientProductController;
use App\Http\Controllers\Admin\CategoryController;
use Illuminate\Support\Facades\Route;

// Client
Route::get('/', function () {
    return redirect()->route('client.products.index');
});

// Trang chi tiết sản phẩm
Route::get('/product/{slug}', [ClientProductController::class, 'show'])
    ->name('client.products.show');

// Trang danh sách sản phẩm
Route::get('/products/{slug?}', [ClientProductController::class, 'index'])
    ->name('client.products.index');

// Admin
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.products.index');
    })->name('dashboard');

    Route::resource('products', AdminProductController::class)->except(['show']);
    Route::resource('categories', CategoryController::class)->except(['show']);
});
