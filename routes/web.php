<?php

use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Client\ProductController as ClientProductController;
use App\Http\Controllers\Admin\CategoryController;
use Illuminate\Support\Facades\Route;

// 1. Route cho Client (Nằm ngoài nhóm admin)
Route::get('/', function () {
    return redirect()->route('client.products.index');
});

Route::get('/products/{slug?}', [ClientProductController::class, 'index'])
    ->name('client.products.index');

// 2. Route cho Admin (Nằm trong nhóm admin)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.products.index'); // Lưu ý: trỏ về đúng route admin
    })->name('dashboard');

    Route::resource('products', AdminProductController::class)->except(['show']);
    Route::resource('categories', CategoryController::class)->except(['show']);
});