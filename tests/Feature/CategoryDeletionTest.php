<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CategoryDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_delete_a_category_that_still_has_products(): void
    {
        [$admin, $category, $product] = $this->fixture(withProduct: true);
        $message = "Không thể xóa danh mục vì vẫn còn sản phẩm thuộc danh mục này.\nVui lòng chuyển hoặc xóa các sản phẩm trước.";

        $this->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('error', $message);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'category_id' => $category->id,
        ]);
    }

    public function test_admin_can_delete_an_empty_category(): void
    {
        [$admin, $category] = $this->fixture();

        $this->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    /**
     * @return array{User, Category, Product|null}
     */
    private function fixture(bool $withProduct = false): array
    {
        $suffix = Str::lower(Str::random(8));
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => true,
        ]);
        $category = Category::create([
            'name' => 'Danh mục kiểm tra xóa',
            'slug' => "danh-muc-xoa-{$suffix}",
            'status' => true,
        ]);
        $product = null;

        if ($withProduct) {
            $product = Product::create([
                'category_id' => $category->id,
                'name' => 'Sản phẩm còn trong danh mục',
                'slug' => "san-pham-con-lai-{$suffix}",
                'status' => true,
                'base_price' => 250000,
            ]);
        }

        return [$admin, $category, $product];
    }
}
