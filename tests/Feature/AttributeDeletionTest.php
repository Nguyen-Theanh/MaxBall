<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AttributeDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_delete_an_attribute_used_by_product_variants(): void
    {
        [$admin, $attribute, $value, $variant] = $this->fixture(withVariant: true);
        $message = "Không thể xóa thuộc tính vì vẫn còn biến thể sản phẩm đang sử dụng thuộc tính này.\nVui lòng cập nhật hoặc xóa các biến thể sản phẩm trước.";

        $this->actingAs($admin)
            ->delete(route('admin.attributes.destroy', $attribute))
            ->assertSessionHas('error', $message);

        $this->assertDatabaseHas('attributes', ['id' => $attribute->id]);
        $this->assertDatabaseHas('attribute_values', ['id' => $value->id]);
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id]);
    }

    public function test_admin_cannot_delete_an_attribute_value_used_by_product_variants(): void
    {
        [$admin, , $value, $variant] = $this->fixture(withVariant: true);
        $message = "Không thể xóa giá trị thuộc tính vì vẫn còn biến thể sản phẩm đang sử dụng giá trị này.\nVui lòng cập nhật hoặc xóa các biến thể sản phẩm trước.";

        $this->actingAs($admin)
            ->delete(route('admin.attributes.values.destroy', $value))
            ->assertSessionHas('error', $message);

        $this->assertDatabaseHas('attribute_values', ['id' => $value->id]);
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id]);
    }

    public function test_admin_must_remove_unused_values_before_deleting_an_attribute(): void
    {
        [$admin, $attribute, $value] = $this->fixture();
        $message = "Không thể xóa thuộc tính vì vẫn còn các giá trị thuộc tính.\nVui lòng xóa các giá trị trước.";

        $this->actingAs($admin)
            ->delete(route('admin.attributes.destroy', $attribute))
            ->assertSessionHas('error', $message);

        $this->assertDatabaseHas('attributes', ['id' => $attribute->id]);
        $this->assertDatabaseHas('attribute_values', ['id' => $value->id]);
    }

    public function test_admin_can_delete_unused_value_then_empty_attribute(): void
    {
        [$admin, $attribute, $value] = $this->fixture();

        $this->actingAs($admin)
            ->delete(route('admin.attributes.values.destroy', $value))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('attribute_values', ['id' => $value->id]);

        $this->delete(route('admin.attributes.destroy', $attribute))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('attributes', ['id' => $attribute->id]);
    }

    /**
     * @return array{User, Attribute, AttributeValue, ProductVariant|null}
     */
    private function fixture(bool $withVariant = false): array
    {
        $suffix = Str::lower(Str::random(8));
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => true,
        ]);
        $attribute = Attribute::create([
            'name' => "Màu sắc {$suffix}",
        ]);
        $value = $attribute->values()->create([
            'value' => 'Đỏ',
        ]);
        $variant = null;

        if ($withVariant) {
            $category = Category::create([
                'name' => 'Danh mục thuộc tính',
                'slug' => "danh-muc-thuoc-tinh-{$suffix}",
                'status' => true,
            ]);
            $product = Product::create([
                'category_id' => $category->id,
                'name' => 'Sản phẩm dùng thuộc tính',
                'slug' => "san-pham-thuoc-tinh-{$suffix}",
                'status' => true,
                'base_price' => 300000,
            ]);
            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'name' => 'Đỏ - M',
                'sku' => "SP-DO-M-{$suffix}",
                'base_price' => 300000,
                'stock' => 5,
            ]);
        }

        return [$admin, $attribute, $value, $variant];
    }
}
