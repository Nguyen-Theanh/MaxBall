<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$html = view('admin.returns.index', ['returns' => App\Models\OrderReturn::paginate(15)])
        ->withErrors(new \Illuminate\Support\MessageBag())
        ->render();
file_put_contents('test_output.html', $html);
