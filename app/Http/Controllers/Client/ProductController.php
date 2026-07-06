<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function home()
    {
        $products = Product::where('status', 1)->latest()->take(8)->get();
        return view('client.products.index', compact('products'));
    }

    public function index(Request $request)
    {
        $query = Product::query()->where('status', 1);
        $categoryName = 'Tất cả sản phẩm';

        if ($request->filled('min_price')) {
            $query->where('base_price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('base_price', '<=', $request->max_price);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->paginate(12)->withQueryString();
        return view('client.products.listing', compact('products', 'categoryName'));
    }

    public function category(Request $request, $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $query = Product::where('status', 1)->where('category_id', $category->id);
        $categoryName = $category->name;

        if ($request->filled('min_price')) {
            $query->where('base_price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('base_price', '<=', $request->max_price);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->paginate(12)->withQueryString();
        return view('client.products.listing', compact('products', 'categoryName'));
    }
    public function show($slug)
    {
        $product = \App\Models\Product::with(['category', 'productImages', 'variants'])
            ->where('slug', $slug)
            ->where('status', 1)
            ->firstOrFail();

        $relatedProducts = \App\Models\Product::where('status', 1)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get();

        return view('client.products.show', compact('product', 'relatedProducts'));
    }
}
