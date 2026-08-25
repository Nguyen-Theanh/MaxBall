<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function home()
    {
        $products = Product::where('status', 1)->latest()->take(8)->get();

        $completedOrdersThisMonth = function ($query): void {
            $query->where('order_status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        };

        $topCustomers = User::whereHas('orders', function ($query): void {
            $query->where('order_status', 'completed')
                ->where('total_amount', '>', 0)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        })
            ->withSum(['orders' => $completedOrdersThisMonth], 'total_amount')
            ->orderByDesc('orders_sum_total_amount')
            ->take(5)
            ->get();

        return view('client.products.index', compact('products', 'topCustomers'));
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
            $query->where('name', 'like', '%'.$request->search.'%');
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
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $products = $query->paginate(12)->withQueryString();

        return view('client.products.listing', compact('products', 'categoryName'));
    }

    public function show($slug)
    {
        $product = Product::with([
            'category',
            'productImages',
            'variants',
        ])
            ->withCount([
                'reviews' => fn ($query) => $query->visible(),
            ])
            ->withAvg([
                'reviews' => fn ($query) => $query->visible(),
            ], 'rating')
            ->where('slug', $slug)
            ->where('status', 1)
            ->firstOrFail();

        $reviews = $product->reviews()
            ->visible()
            ->with(['user', 'orderDetail.variant', 'media'])
            ->latest()
            ->paginate(8, ['*'], 'reviews_page')
            ->withQueryString()
            ->fragment('product-reviews');

        $attributes = Attribute::with('values')->orderBy('name')->get();

        $relatedProducts = Product::where('status', 1)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get();

        return view('client.products.show', compact('product', 'relatedProducts', 'attributes', 'reviews'));
    }
}
