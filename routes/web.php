<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('client.products.index');
});

Route::get('/products', [ProductController::class, 'index'])
    ->name('client.products.index');
