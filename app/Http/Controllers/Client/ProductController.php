<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request, $slug = null)
    {
        $query = Product::query()->where('status', 1);
        $categoryName = 'Tất cả sản phẩm';

        // Lọc giá (dùng chung cho cả trang chủ và trang danh mục)
        if ($request->filled('min_price')) {
            $query->where('base_price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('base_price', '<=', $request->max_price);
        }

        // TÍNH NĂNG MỚI: TÌM KIẾM THEO TÊN
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }



        // KIỂM TRA ĐIỀU KIỆN ĐỂ TRẢ VỀ ĐÚNG FILE VIEW
        if ($slug) {
            // TRƯỜNG HỢP 1: Có slug (VD: /products/ao-quoc-gia) -> Vào trang Danh mục
            $category = Category::where('slug', $slug)->firstOrFail();
            $query->where('category_id', $category->id);
            $categoryName = $category->name;

            $products = $query->paginate(12)->withQueryString();

            // Trả về file mới không có banner
            return view('client.products.listing', compact('products', 'categoryName'));
        }

        // TRƯỜNG HỢP 2: Không có slug (VD: / hoặc /products) -> Vào trang Chủ
        $products = $query->paginate(12)->withQueryString();

        // Trả về file cũ có đầy đủ banner Hero Section
        return view('client.products.index', compact('products', 'categoryName'));
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
