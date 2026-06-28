<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $priceMin = $request->input('price_min');
        $priceMax = $request->input('price_max');

        $products = Product::with('category')
            ->where('status', true)
            ->when(is_numeric($priceMin), function ($query) use ($priceMin) {
                return $query->whereRaw('COALESCE(discount_price, base_price) >= ?', [$priceMin]);
            })
            ->when(is_numeric($priceMax), function ($query) use ($priceMax) {
                return $query->whereRaw('COALESCE(discount_price, base_price) <= ?', [$priceMax]);
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('client.products.index', compact('products', 'priceMin', 'priceMax'));
    }
}
