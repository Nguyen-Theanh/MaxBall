<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminOrderPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_orders_are_paginated_and_keep_filters_in_page_links(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => true,
        ]);
        $customer = User::factory()->create([
            'role' => 'customer',
            'status' => true,
        ]);

        for ($index = 1; $index <= 12; $index++) {
            $this->createOrder($customer, $index);
        }

        $firstPageResponse = $this->actingAs($admin)->get(route('admin.orders.index', [
            'status' => 'pending',
            'search' => 'PAGE-ORDER',
            'per_page' => 10,
        ]))->assertOk();
        $firstPage = $firstPageResponse->viewData('orders');

        $this->assertInstanceOf(LengthAwarePaginator::class, $firstPage);
        $this->assertSame(10, $firstPage->perPage());
        $this->assertSame(10, $firstPage->count());
        $this->assertSame(12, $firstPage->total());
        $this->assertStringContainsString('status=pending', $firstPage->nextPageUrl());
        $this->assertStringContainsString('search=PAGE-ORDER', $firstPage->nextPageUrl());
        $this->assertStringContainsString('per_page=10', $firstPage->nextPageUrl());
        $firstPageResponse
            ->assertSee('Quản lý đơn hàng')
            ->assertDontSee('placeholder="Tìm kiếm đơn hàng, khách hàng..."', false)
            ->assertSee('10 đơn/trang')
            ->assertSee('Hiển thị 1–10 trên tổng 12 đơn hàng')
            ->assertSee('aria-label="Trang sau"', false)
            ->assertDontSee('Showing')
            ->assertDontSee('results');

        $secondPageResponse = $this->get(route('admin.orders.index', [
            'status' => 'pending',
            'search' => 'PAGE-ORDER',
            'per_page' => 10,
            'page' => 2,
        ]))->assertOk();
        $secondPage = $secondPageResponse->viewData('orders');

        $this->assertSame(2, $secondPage->currentPage());
        $this->assertSame(2, $secondPage->count());
        $secondPageResponse->assertSee('Hiển thị 11–12 trên tổng 12 đơn hàng');
    }

    private function createOrder(User $customer, int $index): Order
    {
        return Order::create([
            'user_id' => $customer->id,
            'order_code' => sprintf('PAGE-ORDER-%02d-%s', $index, Str::upper(Str::random(4))),
            'customer_name' => $customer->name,
            'customer_phone' => '0901234567',
            'customer_email' => $customer->email,
            'customer_address' => '123 Nguyễn Huệ, TP. Hồ Chí Minh',
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
