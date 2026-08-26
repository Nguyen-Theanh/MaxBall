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

        $response = $this->actingAs($admin)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('<th class="text-center">Tồn kho</th>', false)
            ->assertSee('<span class="fw-bold text-dark">', false)
            ->assertSee('12')
            ->assertSee('Đang giữ: 3')
            ->assertSee('name="stock_status"', false)
            ->assertSee('Hết hàng')
            ->assertDontSee('Tổng tồn kho:')
            ->assertDontSee('Đang giữ: 0');

        $viewData = $response->original->getData();
        $this->assertArrayNotHasKey('totalStock', $viewData);
        $this->assertArrayNotHasKey('totalReservedStock', $viewData);
    }

    public function test_product_list_can_filter_products_by_available_stock(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => true,
        ]);
        $category = Category::create([
            'name' => 'Danh mục kiểm tra lọc tồn kho',
            'slug' => 'danh-muc-kiem-tra-loc-ton-kho',
            'status' => true,
        ]);
        $inStockProduct = $this->createProduct($category, 'Sản phẩm còn hàng', 'san-pham-con-hang');
        $outOfStockProduct = $this->createProduct($category, 'Sản phẩm hết hàng', 'san-pham-het-hang');
        $fullyReservedProduct = $this->createProduct($category, 'Sản phẩm đã giữ hết', 'san-pham-da-giu-het');
        $partiallyOutOfStockProduct = $this->createProduct($category, 'Sản phẩm có một biến thể hết', 'san-pham-co-mot-bien-the-het');
        $noVariantProduct = $this->createProduct($category, 'Sản phẩm chưa có biến thể', 'san-pham-chua-co-bien-the');

        ProductVariant::create([
            'product_id' => $inStockProduct->id,
            'name' => 'Còn hàng',
            'base_price' => 250000,
            'stock' => 5,
            'reserved_stock' => 1,
        ]);
        ProductVariant::create([
            'product_id' => $outOfStockProduct->id,
            'name' => 'Hết hàng',
            'base_price' => 250000,
            'stock' => 0,
            'reserved_stock' => 0,
        ]);
        ProductVariant::create([
            'product_id' => $fullyReservedProduct->id,
            'name' => 'Đã giữ hết',
            'base_price' => 250000,
            'stock' => 3,
            'reserved_stock' => 3,
        ]);
        ProductVariant::create([
            'product_id' => $partiallyOutOfStockProduct->id,
            'name' => 'Size M còn hàng',
            'base_price' => 250000,
            'stock' => 5,
            'reserved_stock' => 0,
        ]);
        ProductVariant::create([
            'product_id' => $partiallyOutOfStockProduct->id,
            'name' => 'Size L hết hàng',
            'base_price' => 250000,
            'stock' => 0,
            'reserved_stock' => 0,
        ]);

        $outOfStockResponse = $this->actingAs($admin)
            ->get(route('admin.products.index', ['stock_status' => 'out_of_stock']))
            ->assertOk()
            ->assertSee('<th>Biến thể hết hàng</th>', false)
            ->assertDontSee('<th class="text-center">Tồn kho</th>', false)
            ->assertSee('Sản phẩm hết hàng')
            ->assertSee('Sản phẩm đã giữ hết')
            ->assertSee('Sản phẩm có một biến thể hết')
            ->assertSee('Size L hết hàng')
            ->assertDontSee('Size M còn hàng')
            ->assertSee('1 biến thể hết hàng')
            ->assertSee($noVariantProduct->name)
            ->assertSee('Chưa có biến thể')
            ->assertDontSee('Sản phẩm còn hàng');

        $this->assertStringContainsString('<table', $outOfStockResponse->getContent());
        $this->assertTrue(
            strpos($outOfStockResponse->getContent(), 'Thêm sản phẩm')
                < strpos($outOfStockResponse->getContent(), '<table'),
        );

        $this->get(route('admin.products.index', ['stock_status' => 'in_stock']))
            ->assertOk()
            ->assertSee('Sản phẩm còn hàng')
            ->assertDontSee('Sản phẩm hết hàng')
            ->assertDontSee('Sản phẩm đã giữ hết')
            ->assertDontSee('Sản phẩm có một biến thể hết');
    }

    private function createProduct(Category $category, string $name, string $slug): Product
    {
        return Product::create([
            'category_id' => $category->id,
            'name' => $name,
            'slug' => $slug,
            'status' => true,
            'base_price' => 250000,
        ]);
    }
}
