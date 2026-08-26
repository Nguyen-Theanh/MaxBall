<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFlashMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_flash_message_is_rendered_once_and_auto_hides_after_five_seconds(): void
    {
        $admin = $this->admin();
        $message = 'Thông báo kiểm tra chỉ hiển thị một lần.';

        $response = $this->actingAs($admin)
            ->withSession(['success' => $message])
            ->get(route('admin.coupons.index'))
            ->assertOk()
            ->assertSee('data-admin-flash', false)
            ->assertSee('data-admin-flash-close', false)
            ->assertSee('window.setTimeout(dismiss, 5000)', false);

        $this->assertSame(1, substr_count($response->getContent(), $message));
    }

    public function test_admin_error_message_uses_the_shared_auto_hiding_alert(): void
    {
        $admin = $this->admin();
        $message = "Không thể xóa sản phẩm.\nVui lòng thử lại.";

        $response = $this->actingAs($admin)
            ->withSession(['error' => $message])
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('data-admin-flash', false)
            ->assertSee('Không thể xóa sản phẩm.')
            ->assertSee('Vui lòng thử lại.');

        $this->assertSame(1, substr_count($response->getContent(), 'Không thể xóa sản phẩm.'));
    }

    public function test_product_create_and_update_messages_are_vietnamese_with_diacritics(): void
    {
        $admin = $this->admin();
        $category = Category::create([
            'name' => 'Áo bóng đá',
            'slug' => 'ao-bong-da',
            'status' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.products.store'), $this->productPayload($category, 'Áo Việt Nam', 'AO-VIET-NAM'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Đã thêm sản phẩm mới.');

        $product = Product::where('slug', 'ao-viet-nam')->sole();
        $variant = $product->variants()->sole();

        $this->actingAs($admin)
            ->put(route('admin.products.update', $product), $this->productPayload(
                $category,
                'Áo Việt Nam bản mới',
                'AO-VIET-NAM-MOI',
                $variant,
            ))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Đã cập nhật sản phẩm.');
    }

    private function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'status' => true,
        ]);
    }

    /** @return array<string, mixed> */
    private function productPayload(
        Category $category,
        string $name,
        string $sku,
        ?ProductVariant $variant = null,
    ): array {
        return [
            'category_id' => $category->id,
            'name' => $name,
            'base_price' => 200000,
            'status' => '1',
            'variants' => [[
                'id' => $variant?->id,
                'name' => 'M',
                'sku' => $sku,
                'base_price' => 200000,
                'stock' => 10,
            ]],
        ];
    }
}
