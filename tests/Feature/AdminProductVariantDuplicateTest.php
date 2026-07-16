<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductVariantDuplicateTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_update_rejects_duplicate_variant_name_and_sku(): void
    {
        [$admin, $category, $product, $variant] = $this->productFixture();

        $response = $this->actingAs($admin)
            ->from(route('admin.products.edit', $product))
            ->put(route('admin.products.update', $product), $this->productPayload($category, [
                [
                    'id' => $variant->id,
                    'name' => 'Đỏ - M',
                    'sku' => 'AO-DO-M',
                    'base_price' => 200000,
                    'stock' => 10,
                ],
                [
                    'name' => '  đỏ  -  m  ',
                    'sku' => 'ao-do-m',
                    'base_price' => 200000,
                    'stock' => 5,
                ],
            ]));

        $response
            ->assertRedirect(route('admin.products.edit', $product))
            ->assertSessionHasErrors([
                'variants.1.name',
                'variants.1.sku',
            ]);

        $this->assertDatabaseCount('product_variants', 1);
        $this->assertDatabaseHas('product_variants', [
            'id' => $variant->id,
            'name' => 'Đỏ - M',
            'sku' => 'AO-DO-M',
        ]);
    }

    public function test_product_update_still_accepts_distinct_variants(): void
    {
        [$admin, $category, $product, $variant] = $this->productFixture();

        $this->actingAs($admin)
            ->put(route('admin.products.update', $product), $this->productPayload($category, [
                [
                    'id' => $variant->id,
                    'name' => 'Đỏ - M',
                    'sku' => 'AO-DO-M',
                    'base_price' => 200000,
                    'stock' => 10,
                ],
                [
                    'name' => 'Xanh - L',
                    'sku' => 'AO-XANH-L',
                    'base_price' => 210000,
                    'stock' => 5,
                ],
            ]))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertDatabaseCount('product_variants', 2);
        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'name' => 'Xanh - L',
            'sku' => 'AO-XANH-L',
        ]);
    }

    public function test_edit_form_contains_client_side_duplicate_feedback(): void
    {
        [$admin, , $product] = $this->productFixture();

        $this->actingAs($admin)
            ->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('variant-duplicate-alert', false)
            ->assertSee('initializeAttributesFromExistingVariants', false)
            ->assertSee('hasCompleteVariantSelection', false)
            ->assertSee('Các biến thể tương ứng đã tồn tại', false);
    }

    /**
     * @return array{User, Category, Product, ProductVariant}
     */
    private function productFixture(): array
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => true,
        ]);
        $category = Category::create([
            'name' => 'Áo bóng đá',
            'slug' => 'ao-bong-da',
            'status' => true,
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Áo kiểm thử',
            'slug' => 'ao-kiem-thu',
            'status' => true,
            'base_price' => 200000,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Đỏ - M',
            'sku' => 'AO-DO-M',
            'base_price' => 200000,
            'stock' => 10,
        ]);

        return [$admin, $category, $product, $variant];
    }

    /**
     * @param  array<int, array<string, int|string|null>>  $variants
     * @return array<string, mixed>
     */
    private function productPayload(Category $category, array $variants): array
    {
        return [
            'category_id' => $category->id,
            'name' => 'Áo kiểm thử',
            'slug' => 'ao-kiem-thu',
            'base_price' => 200000,
            'status' => '1',
            'variants' => $variants,
        ];
    }
}
