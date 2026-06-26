<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')
            ->where('status', true)
            ->latest()
            ->paginate(12);

        return view('client.products.index', compact('products'));
    }
}
