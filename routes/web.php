<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\PromotionAnnouncementController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Client\CartController;
use App\Http\Controllers\Client\ChatbotController;
use App\Http\Controllers\Client\CheckoutController;
use App\Http\Controllers\Client\ContactController;
use App\Http\Controllers\Client\OrderController as ClientOrderController;
use App\Http\Controllers\Client\PageController;
use App\Http\Controllers\Client\PaymentController;
use App\Http\Controllers\Client\ProductController as ClientProductController;
use App\Http\Controllers\Client\ReviewController;
use App\Http\Controllers\Client\UserAddressController;
use App\Http\Controllers\Client\VietnamAddressController;
use App\Http\Controllers\Client\VoucherController;
use Illuminate\Support\Facades\Route;

// Client
Route::get('/', [ClientProductController::class, 'home'])->name('client.home');

Route::post('/api/chatbot', ChatbotController::class)
    ->middleware('throttle:chatbot')
    ->name('api.chatbot');

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
    Route::get('/api/vietnam-address/provinces', [VietnamAddressController::class, 'provinces'])
        ->name('api.vietnam-address.provinces');
    Route::get('/api/vietnam-address/wards', [VietnamAddressController::class, 'wards'])
        ->name('api.vietnam-address.wards');

    Route::get('/account', [AccountController::class, 'show'])->name('account.show');
    Route::put('/account', [AccountController::class, 'update'])->name('account.update');
    Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('account.password.update');

    // Sổ địa chỉ
    Route::post('/account/addresses', [UserAddressController::class, 'store'])->name('account.addresses.store');
    Route::put('/account/addresses/{address}', [UserAddressController::class, 'update'])->name('account.addresses.update');
    Route::delete('/account/addresses/{address}', [UserAddressController::class, 'destroy'])->name('account.addresses.destroy');
    Route::patch('/account/addresses/{address}/default', [UserAddressController::class, 'setDefault'])->name('account.addresses.setDefault');

    // Giỏ hàng
    Route::get('/gio-hang', [CartController::class, 'index'])->name('client.cart.index');
    Route::post('/gio-hang', [CartController::class, 'store'])->name('client.cart.store');
    Route::put('/gio-hang/{cartItem}', [CartController::class, 'update'])->name('client.cart.update');
    Route::delete('/gio-hang/{cartItem}', [CartController::class, 'destroy'])->name('client.cart.destroy');

    // Thanh toán
    Route::post('/thanh-toan/prepare', [CheckoutController::class, 'prepare'])->name('client.checkout.prepare');
    Route::get('/thanh-toan', [CheckoutController::class, 'index'])->name('client.checkout.index');
    Route::post('/thanh-toan', [CheckoutController::class, 'store'])->name('client.checkout.store');

    Route::get('/vouchers/active', [VoucherController::class, 'getActiveVouchers'])->name('vouchers.active');
    Route::post('/vouchers/save', [VoucherController::class, 'saveVoucher'])->name('vouchers.save');
    Route::post('/vouchers/validate', [VoucherController::class, 'validateVoucher'])->name('vouchers.validate');
    Route::get('/thanh-toan/vnpay-return', [CheckoutController::class, 'vnpayReturn'])->name('client.checkout.vnpay_return');
    Route::get('/thanh-toan/momo-return', [CheckoutController::class, 'momoReturn'])->name('client.checkout.momo_return');

    // QR Payment Routes
    Route::get('/thanh-toan/qr/{order_code}', [PaymentController::class, 'showQr'])->name('client.checkout.payment_qr');
    Route::get('/thanh-toan/check-status/{order_code}', [PaymentController::class, 'checkStatus'])->name('client.checkout.check_status');
    Route::get('/thanh-toan/thanh-cong/{order_code}', [PaymentController::class, 'success'])->name('client.checkout.success');

    // Quản lý đơn hàng (Client)
    Route::get('/don-hang', [ClientOrderController::class, 'index'])->name('client.orders.index');
    Route::get('/don-hang/{id}', [ClientOrderController::class, 'show'])->name('client.orders.show');
    Route::put('/don-hang/{id}/cancel', [ClientOrderController::class, 'cancel'])->name('client.orders.cancel');
    Route::put('/don-hang/{id}/confirm-receipt', [ClientOrderController::class, 'confirmReceipt'])->name('client.orders.confirmReceipt');
    Route::post('/don-hang/{order}/san-pham/{orderDetail}/danh-gia', [ReviewController::class, 'store'])
        ->name('client.orders.reviews.store');
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
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export/excel', [DashboardController::class, 'exportExcel'])->name('dashboard.export.excel');
    Route::get('/dashboard/export/pdf', [DashboardController::class, 'exportPdf'])->name('dashboard.export.pdf');

    Route::get('/products/check-name', [AdminProductController::class, 'checkName'])->name('products.check-name');
    Route::resource('products', AdminProductController::class);
    Route::delete('product-images/{id}', [AdminProductController::class, 'destroyImage'])->name('product-images.destroy');
    Route::get('/coupons/check-code', [CouponController::class, 'checkCode'])->name('coupons.check-code');
    Route::resource('coupons', CouponController::class);
    Route::post('coupons/{coupon}/toggle-status', [CouponController::class, 'toggleStatus'])->name('coupons.toggle-status');
    Route::resource('promotion-announcements', PromotionAnnouncementController::class)->except(['show']);
    Route::patch('promotion-announcements/{promotionAnnouncement}/toggle-status', [PromotionAnnouncementController::class, 'toggleStatus'])
        ->name('promotion-announcements.toggle-status');

    Route::get('/categories/check-name', [CategoryController::class, 'checkName'])->name('categories.check-name');
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('users', AdminUserController::class)->only(['index']);
    Route::patch('users/{user}/toggle-role', [AdminUserController::class, 'toggleRole'])->name('users.toggle-role');
    Route::patch('users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');

    // Quản lý đơn hàng
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/packing-slips', [AdminOrderController::class, 'packingSlips'])->name('orders.packing-slips');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::patch('/orders/{order}/payment-status', [AdminOrderController::class, 'updatePaymentStatus'])->name('orders.updatePaymentStatus');

    // Quản lý đánh giá
    Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::post('/reviews', [AdminReviewController::class, 'store'])->name('reviews.store');
    Route::patch('/reviews/{review}/visibility', [AdminReviewController::class, 'updateVisibility'])->name('reviews.visibility');

    // Quản lý thuộc tính và biến thể
    Route::resource('attributes', AttributeController::class)->only(['index', 'store', 'destroy']);
    Route::post('attributes/{attribute}/values', [AttributeController::class, 'storeValue'])->name('attributes.values.store');
    Route::delete('attribute-values/{value}', [AttributeController::class, 'destroyValue'])->name('attributes.values.destroy');
    Route::post('attribute-values/reorder', [AttributeController::class, 'updateOrder'])->name('attributes.values.reorder');

    // Quản lý liên hệ
    Route::get('/contacts', [App\Http\Controllers\Admin\ContactController::class, 'index'])->name('contacts.index');
    Route::get('/contacts/{contact}', [App\Http\Controllers\Admin\ContactController::class, 'show'])->name('contacts.show');
    Route::patch('/contacts/{contact}/status', [App\Http\Controllers\Admin\ContactController::class, 'updateStatus'])->name('contacts.updateStatus');
    Route::delete('/contacts/{contact}', [App\Http\Controllers\Admin\ContactController::class, 'destroy'])->name('contacts.destroy');
});

// Webhook Route (No CSRF needed, configure in bootstrap/app.php)
Route::post('/webhook/sepay', [PaymentController::class, 'sepayWebhook'])->name('webhook.sepay');
