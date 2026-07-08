<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Client\ProductController as ClientProductController;
use App\Http\Controllers\Client\PageController;
use App\Http\Controllers\Client\ContactController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use Illuminate\Support\Facades\Route;

// Client
Route::get('/', [ClientProductController::class, 'home'])->name('client.home');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/account', [AccountController::class, 'show'])->name('account.show');
    Route::put('/account', [AccountController::class, 'update'])->name('account.update');
    Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('account.password.update');
});

// Trang danh sách tất cả sản phẩm
Route::get('/san-pham', [ClientProductController::class, 'index'])
    ->name('client.products.index');

// Trang Về Chúng Tôi
Route::get('/ve-chung-toi', [PageController::class, 'about'])->name('client.about');

// Trang Liên Hệ
Route::get('/lien-he', [ContactController::class, 'index'])->name('client.contact');
Route::post('/lien-he', [ContactController::class, 'submit'])->name('client.contact.submit')->middleware(['auth', 'throttle:3,1']);

// Trang danh mục sản phẩm
Route::get('/danh-muc/{slug}', [ClientProductController::class, 'category'])
    ->name('client.category.show');

// Trang chi tiết sản phẩm
Route::get('/san-pham/{slug}', [ClientProductController::class, 'show'])
    ->name('client.products.show');

// Admin
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.products.index');
    })->name('dashboard');

    Route::resource('products', AdminProductController::class)->except(['show']);
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('users', AdminUserController::class)->except(['show']);
    
    // Quản lý liên hệ
    Route::get('/contacts', [\App\Http\Controllers\Admin\ContactController::class, 'index'])->name('contacts.index');
    Route::get('/contacts/{contact}', [\App\Http\Controllers\Admin\ContactController::class, 'show'])->name('contacts.show');
    Route::patch('/contacts/{contact}/status', [\App\Http\Controllers\Admin\ContactController::class, 'updateStatus'])->name('contacts.updateStatus');
    Route::delete('/contacts/{contact}', [\App\Http\Controllers\Admin\ContactController::class, 'destroy'])->name('contacts.destroy');
});
