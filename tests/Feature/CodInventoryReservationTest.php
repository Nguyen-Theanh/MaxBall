<?php

namespace Tests\Feature;

use App\Mail\OrderCancelledMail;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserVoucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class CodInventoryReservationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_cod_checkout_holds_stock_for_24_hours_without_deducting_inventory(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-08-25 10:00:00');

        [$customer, $address, $variant] = $this->checkoutFixture(10, 2);

        $this->actingAs($customer)
            ->post(route('client.checkout.store'), [
                'user_address_id' => $address->id,
                'payment_method' => 'cod',
            ])
            ->assertRedirect(route('client.orders.index'))
            ->assertSessionHas('success');

        $order = Order::sole();

        $this->assertSame('pending', $order->order_status);
        $this->assertSame('cod', $order->payment_method);
        $this->assertTrue($order->hasActiveReservation());
        $this->assertTrue($order->reservation_expires_at->equalTo(now()->addHours(24)));
        $this->assertSame(10, $variant->refresh()->stock);
        $this->assertSame(2, $variant->reserved_stock);
        $this->assertSame(8, $variant->available_stock);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_shop_confirmation_commits_held_stock_and_uses_confirmed_status(): void
    {
        Mail::fake();
        [$customer, $address, $variant] = $this->checkoutFixture(10, 2);
        $order = $this->placeCodOrder($customer, $address);
        $admin = User::factory()->create(['role' => 'admin', 'status' => true]);

        $this->actingAs($admin)
            ->patch(route('admin.orders.updateStatus', $order), [
                'order_status' => 'confirmed',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertSame('confirmed', $order->refresh()->order_status);
        $this->assertNotNull($order->inventory_committed_at);
        $this->assertSame(8, $variant->refresh()->stock);
        $this->assertSame(0, $variant->reserved_stock);
        $this->assertSame(8, $variant->available_stock);
    }

    public function test_shop_rejection_releases_hold_without_increasing_physical_stock(): void
    {
        Mail::fake();
        [$customer, $address, $variant] = $this->checkoutFixture(10, 2);
        $order = $this->placeCodOrder($customer, $address);
        $admin = User::factory()->create(['role' => 'admin', 'status' => true]);

        $this->actingAs($admin)
            ->patch(route('admin.orders.updateStatus', $order), [
                'order_status' => 'cancelled',
                'cancellation_reason' => 'out_of_stock',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertSame('cancelled', $order->refresh()->order_status);
        $this->assertNotNull($order->inventory_released_at);
        $this->assertSame(10, $variant->refresh()->stock);
        $this->assertSame(0, $variant->reserved_stock);
        $this->assertSame(10, $variant->available_stock);
    }

    public function test_cancelling_a_confirmed_cod_order_restores_committed_stock(): void
    {
        Mail::fake();
        [$customer, $address, $variant] = $this->checkoutFixture(10, 2);
        $order = $this->placeCodOrder($customer, $address);
        $admin = User::factory()->create(['role' => 'admin', 'status' => true]);

        $this->actingAs($admin)->patch(route('admin.orders.updateStatus', $order), [
            'order_status' => 'confirmed',
        ]);
        $this->assertSame(8, $variant->refresh()->stock);

        $this->actingAs($customer)
            ->put(route('client.orders.cancel', $order), [
                'cancellation_reason' => 'changed_mind',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertSame('cancelled', $order->refresh()->order_status);
        $this->assertSame(10, $variant->refresh()->stock);
        $this->assertSame(0, $variant->reserved_stock);
        $this->assertNotNull($order->inventory_released_at);
    }

    public function test_expired_cod_order_is_cancelled_and_releases_held_stock(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-08-25 10:00:00');
        [$customer, $address, $variant] = $this->checkoutFixture(10, 3);
        $order = $this->placeCodOrder($customer, $address);
        $coupon = Coupon::create([
            'code' => 'EXPIRED-COD-FREESHIP',
            'description' => 'Voucher kiểm thử COD hết hạn',
            'discount_type' => 'freeship',
            'discount_value' => 0,
            'min_order_value' => 0,
            'usage_limit' => 1,
            'used_count' => 1,
            'start_date' => null,
            'expires_at' => null,
            'status' => true,
            'is_public' => false,
        ]);
        $userVoucher = UserVoucher::create([
            'user_id' => $customer->id,
            'coupon_id' => $coupon->id,
            'is_used' => true,
            'used_at' => now(),
        ]);
        $order->update(['freeship_coupon_id' => $coupon->id]);

        Carbon::setTestNow('2026-08-26 10:00:01');

        $this->artisan('orders:expire-cod-reservations')
            ->expectsOutput('Đã tự hủy 1 đơn COD quá hạn và nhả hàng đang giữ.')
            ->assertSuccessful();

        $order->refresh();
        $this->assertSame('cancelled', $order->order_status);
        $this->assertSame('system', $order->cancelled_by);
        $this->assertSame('confirmation_timeout', $order->cancellation_reason);
        $this->assertSame(10, $variant->refresh()->stock);
        $this->assertSame(0, $variant->reserved_stock);
        $this->assertFalse($userVoucher->refresh()->is_used);
        $this->assertNull($userVoucher->used_at);
        $this->assertSame(0, $coupon->refresh()->used_count);
        Mail::assertSent(OrderCancelledMail::class);
    }

    public function test_a_second_customer_cannot_buy_stock_held_by_an_active_cod_order(): void
    {
        Mail::fake();
        [$firstCustomer, $firstAddress, $variant] = $this->checkoutFixture(10, 8);
        $this->placeCodOrder($firstCustomer, $firstAddress);

        [$secondCustomer, $secondAddress] = $this->customerCartFixture($variant, 3);

        $this->actingAs($secondCustomer)
            ->from(route('client.checkout.index'))
            ->post(route('client.checkout.store'), [
                'user_address_id' => $secondAddress->id,
                'payment_method' => 'cod',
            ])
            ->assertRedirect(route('client.checkout.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 1);
        $this->assertSame(10, $variant->refresh()->stock);
        $this->assertSame(8, $variant->reserved_stock);
        $this->assertSame(2, $variant->available_stock);
    }

    /**
     * @return array{User, UserAddress, ProductVariant}
     */
    private function checkoutFixture(int $stock, int $quantity): array
    {
        $suffix = Str::lower(Str::random(8));
        $category = Category::create([
            'name' => 'Danh mục COD '.$suffix,
            'slug' => 'danh-muc-cod-'.$suffix,
            'status' => true,
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Sản phẩm COD '.$suffix,
            'slug' => 'san-pham-cod-'.$suffix,
            'status' => true,
            'base_price' => 200000,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Size M',
            'base_price' => 200000,
            'stock' => $stock,
        ]);

        [$customer, $address] = $this->customerCartFixture($variant, $quantity);

        return [$customer, $address, $variant];
    }

    /**
     * @return array{User, UserAddress}
     */
    private function customerCartFixture(ProductVariant $variant, int $quantity): array
    {
        $customer = User::factory()->create(['status' => true]);
        $address = UserAddress::create([
            'user_id' => $customer->id,
            'receiver_name' => $customer->name,
            'receiver_phone' => '0901234567',
            'receiver_email' => $customer->email,
            'address_detail' => '123 Nguyễn Huệ, Thành phố Hồ Chí Minh',
            'is_default' => true,
        ]);
        $cart = Cart::create(['user_id' => $customer->id]);
        $cart->items()->create([
            'product_variant_id' => $variant->id,
            'quantity' => $quantity,
        ]);

        return [$customer, $address];
    }

    private function placeCodOrder(User $customer, UserAddress $address): Order
    {
        $this->actingAs($customer)
            ->post(route('client.checkout.store'), [
                'user_address_id' => $address->id,
                'payment_method' => 'cod',
            ])
            ->assertSessionHasNoErrors();

        return Order::where('user_id', $customer->id)->sole();
    }
}
