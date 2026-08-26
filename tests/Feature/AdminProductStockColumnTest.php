<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductStockColumnTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_list_displays_total_stock_for_each_product(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => true,
        ]);
        $category = Category::create([
            'name' => 'Danh mục kiểm tra cột tồn kho',
            'slug' => 'danh-muc-kiem-tra-cot-ton-kho',
            'status' => true,
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Sản phẩm kiểm tra cột tồn kho',
            'slug' => 'san-pham-kiem-tra-cot-ton-kho',
            'status' => true,
            'base_price' => 250000,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Đỏ - M',
            'base_price' => 250000,
            'stock' => 7,
            'reserved_stock' => 3,
        ]);
        ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Xanh - L',
            'base_price' => 250000,
            'stock' => 5,
            'reserved_stock' => 0,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('<th class="text-center">Tồn kho</th>', false)
            ->assertSee('<span class="fw-bold text-dark">', false)
            ->assertSee('12')
            ->assertSee('Đang giữ: 3');
    }
}
