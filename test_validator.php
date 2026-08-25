<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/', 'GET');
$kernel->handle($request);

$validator = Illuminate\Support\Facades\Validator::make(
    ['name' => 'test'],
    ['name' => [
        'required',
        \Illuminate\Validation\Rule::unique('products')->ignore(null)
    ]]
);
try {
    $validator->validate();
    echo "Passed!";
} catch (\Throwable $e) {
    echo get_class($e) . ': ' . $e->getMessage();
}
