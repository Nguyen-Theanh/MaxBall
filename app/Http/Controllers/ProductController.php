<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')
            ->where('status', true)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get();

        return view('client.products.index', compact('products'));
    }
}
