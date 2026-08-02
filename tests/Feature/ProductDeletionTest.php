<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_delete_a_product_that_has_generated_an_order(): void
    {
        [$admin, $product, $variant, $order, $detail] = $this->fixture(withOrder: true);
        $message = "Không thể xóa sản phẩm vì sản phẩm này đã phát sinh đơn hàng.\nVui lòng ẩn sản phẩm thay vì xóa để bảo toàn dữ liệu đơn hàng.";

        $this->actingAs($admin)
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('error', $message);

        $this->assertDatabaseHas('products', ['id' => $product->id]);
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id]);
        $this->assertDatabaseHas('orders', ['id' => $order->id]);
        $this->assertDatabaseHas('order_details', [
            'id' => $detail->id,
            'product_variant_id' => $variant->id,
        ]);
    }

    public function test_admin_can_delete_a_product_that_has_never_generated_an_order(): void
    {
        [$admin, $product, $variant] = $this->fixture();

        $this->actingAs($admin)
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseMissing('product_variants', ['id' => $variant->id]);
    }

    /**
     * @return array{User, Product, ProductVariant, Order|null, OrderDetail|null}
     */
    private function fixture(bool $withOrder = false): array
    {
        $suffix = Str::lower(Str::random(8));
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => true,
        ]);
        $customer = User::factory()->create();
        $category = Category::create([
            'name' => 'Danh mục kiểm tra xóa sản phẩm',
            'slug' => "danh-muc-xoa-san-pham-{$suffix}",
            'status' => true,
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Sản phẩm kiểm tra xóa',
            'slug' => "san-pham-kiem-tra-xoa-{$suffix}",
            'status' => true,
            'base_price' => 300000,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Đỏ - M',
            'sku' => "SP-XOA-{$suffix}",
            'base_price' => 300000,
            'stock' => 5,
        ]);
        $order = null;
        $detail = null;

        if ($withOrder) {
            $order = Order::create([
                'user_id' => $customer->id,
                'order_code' => strtoupper(Str::random(10)),
                'customer_name' => $customer->name,
                'customer_phone' => '0901234567',
                'customer_email' => $customer->email,
                'customer_address' => '123 Nguyễn Huệ, Thành phố Hồ Chí Minh',
                'sub_total' => 300000,
                'shipping_fee' => 30000,
                'discount_amount' => 0,
                'total_amount' => 330000,
                'payment_method' => 'cod',
                'payment_status' => 'paid',
                'order_status' => 'completed',
            ]);
            $detail = OrderDetail::create([
                'order_id' => $order->id,
                'product_variant_id' => $variant->id,
                'quantity' => 1,
                'price' => 300000,
            ]);
        }

        return [$admin, $product, $variant, $order, $detail];
    }
}
