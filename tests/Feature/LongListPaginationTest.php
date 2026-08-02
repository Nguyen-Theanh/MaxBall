<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Tests\TestCase;

class LongListPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_category_and_attribute_lists_are_paginated(): void
    {
        $admin = $this->admin();

        for ($index = 1; $index <= 12; $index++) {
            Category::create([
                'name' => sprintf('Danh mục %02d', $index),
                'slug' => sprintf('danh-muc-%02d', $index),
                'status' => true,
            ]);
            Attribute::create([
                'name' => sprintf('Thuộc tính %02d', $index),
            ]);
        }

        $categoryResponse = $this->actingAs($admin)
            ->get(route('admin.categories.index', ['page' => 2]))
            ->assertOk();
        $categories = $categoryResponse->viewData('categories');

        $this->assertInstanceOf(LengthAwarePaginator::class, $categories);
        $this->assertSame(2, $categories->currentPage());
        $this->assertSame(2, $categories->count());
        $this->assertSame(12, $categories->total());

        $attributeResponse = $this->get(route('admin.attributes.index', ['page' => 2]))
            ->assertOk();
        $attributes = $attributeResponse->viewData('attributes');

        $this->assertInstanceOf(LengthAwarePaginator::class, $attributes);
        $this->assertSame(2, $attributes->currentPage());
        $this->assertSame(4, $attributes->count());
        $this->assertSame(12, $attributes->total());
    }

    public function test_customer_order_history_is_paginated(): void
    {
        $customer = User::factory()->create();

        for ($index = 1; $index <= 10; $index++) {
            $this->createOrder($customer, $index);
        }

        $response = $this->actingAs($customer)
            ->get(route('client.orders.index', ['page' => 2]))
            ->assertOk();
        $orders = $response->viewData('orders');

        $this->assertInstanceOf(LengthAwarePaginator::class, $orders);
        $this->assertSame(2, $orders->currentPage());
        $this->assertSame(2, $orders->count());
        $this->assertSame(10, $orders->total());
        $this->assertStringEndsWith('#orders', $orders->url(1));
    }

    public function test_account_uses_independent_pagination_for_orders_and_addresses(): void
    {
        $customer = User::factory()->create();

        for ($index = 1; $index <= 10; $index++) {
            $this->createOrder($customer, $index);
            $customer->addresses()->create([
                'receiver_name' => "Người nhận {$index}",
                'receiver_phone' => '0901234567',
                'receiver_email' => $customer->email,
                'address_detail' => "Địa chỉ nhận hàng {$index}",
                'is_default' => $index === 1,
            ]);
        }

        $response = $this->actingAs($customer)
            ->get(route('account.show', [
                'orders_page' => 2,
                'addresses_page' => 2,
            ]))
            ->assertOk();
        $orders = $response->viewData('orders');
        $addresses = $response->viewData('addresses');

        $response
            ->assertSee('class="flex gap-2 items-center justify-between sm:hidden"', false)
            ->assertDontSee('class="d-flex justify-content-between flex-fill d-sm-none"', false);
        $this->assertSame(2, $orders->currentPage());
        $this->assertSame(2, $addresses->currentPage());
        $this->assertSame(2, $orders->count());
        $this->assertSame(2, $addresses->count());
        $this->assertStringContainsString('orders_page=', $orders->url(1));
        $this->assertStringContainsString('addresses_page=', $addresses->url(1));
        $this->assertStringEndsWith('#orders', $orders->url(1));
        $this->assertStringEndsWith('#address', $addresses->url(1));
    }

    public function test_product_reviews_are_paginated_with_a_section_fragment(): void
    {
        $customer = User::factory()->create();
        $suffix = Str::lower(Str::random(8));
        $category = Category::create([
            'name' => 'Danh mục đánh giá phân trang',
            'slug' => "danh-muc-review-page-{$suffix}",
            'status' => true,
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Sản phẩm đánh giá phân trang',
            'slug' => "san-pham-review-page-{$suffix}",
            'status' => true,
            'base_price' => 250000,
        ]);

        for ($index = 1; $index <= 10; $index++) {
            Review::create([
                'user_id' => $customer->id,
                'product_id' => $product->id,
                'rating' => 5,
                'content' => "Đánh giá phân trang {$index}",
                'is_visible' => true,
            ]);
        }

        $response = $this->get(route('client.products.show', [
            'slug' => $product->slug,
            'reviews_page' => 2,
        ]))->assertOk();
        $reviews = $response->viewData('reviews');

        $this->assertInstanceOf(LengthAwarePaginator::class, $reviews);
        $this->assertSame(2, $reviews->currentPage());
        $this->assertSame(2, $reviews->count());
        $this->assertSame(10, $reviews->total());
        $this->assertStringContainsString('reviews_page=', $reviews->url(1));
        $this->assertStringEndsWith('#product-reviews', $reviews->url(1));
    }

    private function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'status' => true,
        ]);
    }

    private function createOrder(User $customer, int $index): Order
    {
        return Order::create([
            'user_id' => $customer->id,
            'order_code' => sprintf('PAGE-%02d-%s', $index, Str::upper(Str::random(5))),
            'customer_name' => $customer->name,
            'customer_phone' => '0901234567',
            'customer_email' => $customer->email,
            'customer_address' => '123 Nguyễn Huệ, Thành phố Hồ Chí Minh',
            'sub_total' => 200000,
            'shipping_fee' => 30000,
            'discount_amount' => 0,
            'total_amount' => 230000,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'order_status' => 'pending',
        ]);
    }
}
