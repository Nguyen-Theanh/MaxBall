<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/admin/products', 'POST', ['name' => 'test']);
$response = $kernel->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
