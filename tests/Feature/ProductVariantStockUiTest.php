<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductVariantStockUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_sold_out_variant_options_are_dimmed_and_disabled(): void
    {
        $suffix = Str::lower(Str::random(8));
        $color = Attribute::create(['name' => 'Màu sắc']);
        $color->values()->createMany([
            ['value' => 'Đỏ'],
            ['value' => 'Xanh'],
        ]);
        $size = Attribute::create(['name' => 'Kích cỡ']);
        $size->values()->createMany([
            ['value' => 'M'],
            ['value' => 'L'],
        ]);
        $category = Category::create([
            'name' => 'Danh mục kiểm tra tồn kho',
            'slug' => "danh-muc-ton-kho-{$suffix}",
            'status' => true,
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Áo kiểm tra biến thể hết hàng',
            'slug' => "ao-bien-the-het-hang-{$suffix}",
            'status' => true,
            'base_price' => 250000,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Đỏ - M',
            'base_price' => 250000,
            'stock' => 0,
        ]);
        ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Đỏ - L',
            'base_price' => 250000,
            'stock' => 0,
        ]);
        ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Xanh - M',
            'base_price' => 250000,
            'stock' => 5,
        ]);
        ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Xanh - L',
            'base_price' => 250000,
            'stock' => 0,
        ]);

        $content = $this->get(route('client.products.show', $product->slug))
            ->assertOk()
            ->assertSee('disabled:opacity-50', false)
            ->assertSee('disabled:line-through', false)
            ->assertSee('Phân loại này đã hết hàng')
            ->assertSee('Number(variant.stock) > 0', false)
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/<button[^>]*data-value="Đỏ"[^>]*disabled[^>]*aria-disabled="true"/s',
            $content
        );
        $this->assertMatchesRegularExpression(
            '/<button[^>]*data-value="Xanh"[^>]*aria-disabled="false"/s',
            $content
        );
    }
}
