<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPackingSlipPrintStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmed_orders_put_unprinted_orders_first_and_show_printed_label(): void
    {
        $admin = $this->createAdmin();
        $printedOrder = $this->createConfirmedOrder('PRINTED-ORDER', now(), now());
        $unprintedOrder = $this->createConfirmedOrder(
            'UNPRINTED-ORDER',
            now()->subDay()
        );

        $response = $this->actingAs($admin)->get(route('admin.orders.index', [
            'status' => 'confirmed',
        ]))->assertOk();

        $orders = $response->viewData('orders');

        $this->assertSame([
            $unprintedOrder->id,
            $printedOrder->id,
        ], $orders->pluck('id')->all());

        $response
            ->assertSeeInOrder(['#UNPRINTED-ORDER', '#PRINTED-ORDER'])
            ->assertSee('Đã in')
            ->assertSee('In lại');
    }

    public function test_opening_packing_slips_marks_selected_orders_as_printed(): void
    {
        $admin = $this->createAdmin();
        $order = $this->createConfirmedOrder('FIRST-PRINT', now());

        $this->actingAs($admin)
            ->post(route('admin.orders.packing-slips'), [
                'order_ids' => [$order->id],
            ])
            ->assertOk()
            ->assertViewIs('admin.orders.packing-slips');

        $this->assertNotNull($order->refresh()->packing_slip_printed_at);
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'status' => true,
        ]);
    }

    private function createConfirmedOrder(
        string $code,
        $createdAt,
        $printedAt = null
    ): Order {
        $order = Order::create([
            'order_code' => $code,
            'customer_name' => 'Khách kiểm thử',
            'customer_phone' => '0901234567',
            'customer_email' => 'customer@example.com',
            'customer_address' => '123 Nguyễn Huệ, TP. Hồ Chí Minh',
            'sub_total' => 200000,
            'shipping_fee' => 30000,
            'discount_amount' => 0,
            'total_amount' => 230000,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'order_status' => 'confirmed',
            'packing_slip_printed_at' => $printedAt,
        ]);

        $order->timestamps = false;
        $order->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->save();

        return $order;
    }
}
