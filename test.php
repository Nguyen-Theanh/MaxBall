<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/admin/products', 'POST', [
    'name' => 'test',
    'base_price' => 100,
    '_token' => csrf_token() // this will fail without session, let's bypass CSRF
]);
// disable CSRF by clearing middleware
// Actually just catching the exception from store
try {
    $controller = $app->make(App\Http\Controllers\Admin\ProductController::class);
    $controller->store($request);
} catch (\Throwable $e) {
    echo get_class($e) . ': ' . $e->getMessage();
}
