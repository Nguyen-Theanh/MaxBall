<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\UserVoucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class CheckoutVoucherCombinationTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_accepts_one_discount_and_one_freeship_voucher_together(): void
    {
        Mail::fake();
        [$user, $addressId, $variant] = $this->checkoutFixture();
        [$discountCoupon, $discountVoucher] = $this->voucherFixture($user, 'fixed');
        [$freeshipCoupon, $freeshipVoucher] = $this->voucherFixture($user, 'freeship');

        $this->actingAs($user)
            ->from(route('client.checkout.index'))
            ->post(route('client.checkout.store'), [
                'user_address_id' => $addressId,
                'payment_method' => 'cod',
                'coupon_code' => $discountCoupon->code,
                'freeship_coupon_code' => $freeshipCoupon->code,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('client.orders.index'));

        $order = Order::sole();

        $this->assertSame($discountCoupon->id, $order->coupon_id);
        $this->assertSame($freeshipCoupon->id, $order->freeship_coupon_id);
        $this->assertSame('100000.00', $order->discount_amount);
        $this->assertSame('0.00', $order->shipping_fee);
        $this->assertSame(1, $variant->refresh()->reserved_stock);
        $this->assertSame(1, $discountCoupon->refresh()->used_count);
        $this->assertTrue($discountVoucher->refresh()->is_used);
        $this->assertSame(1, $freeshipCoupon->refresh()->used_count);
        $this->assertTrue($freeshipVoucher->refresh()->is_used);
    }

    public function test_checkout_accepts_one_discount_voucher(): void
    {
        Mail::fake();
        [$user, $addressId] = $this->checkoutFixture();
        [$coupon, $userVoucher] = $this->voucherFixture($user, 'fixed');

        $this->actingAs($user)
            ->post(route('client.checkout.store'), [
                'user_address_id' => $addressId,
                'payment_method' => 'cod',
                'coupon_code' => $coupon->code,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('client.orders.index'));

        $order = Order::sole();

        $this->assertSame($coupon->id, $order->coupon_id);
        $this->assertNull($order->freeship_coupon_id);
        $this->assertSame('100000.00', $order->discount_amount);
        $this->assertSame(1, $coupon->refresh()->used_count);
        $this->assertTrue($userVoucher->refresh()->is_used);
    }

    public function test_checkout_accepts_one_freeship_voucher(): void
    {
        Mail::fake();
        [$user, $addressId] = $this->checkoutFixture();
        [$coupon, $userVoucher] = $this->voucherFixture($user, 'freeship');

        $this->actingAs($user)
            ->post(route('client.checkout.store'), [
                'user_address_id' => $addressId,
                'payment_method' => 'cod',
                'freeship_coupon_code' => $coupon->code,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('client.orders.index'));

        $order = Order::sole();

        $this->assertNull($order->coupon_id);
        $this->assertSame($coupon->id, $order->freeship_coupon_id);
        $this->assertSame('0.00', $order->shipping_fee);
        $this->assertSame(1, $coupon->refresh()->used_count);
        $this->assertTrue($userVoucher->refresh()->is_used);
    }

    public function test_percentage_voucher_discount_is_capped_at_maximum_amount(): void
    {
        Mail::fake();
        [$user, $addressId] = $this->checkoutFixture();
        [$coupon, $userVoucher] = $this->voucherFixture($user, 'percent');

        $this->actingAs($user)
            ->postJson(route('vouchers.validate'), ['code' => $coupon->code])
            ->assertOk()
            ->assertJsonPath('coupon.discount_value', '50.00')
            ->assertJsonPath('coupon.max_discount_amount', '100000.00');

        $this->actingAs($user)
            ->post(route('client.checkout.store'), [
                'user_address_id' => $addressId,
                'payment_method' => 'cod',
                'coupon_code' => $coupon->code,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('client.orders.index'));

        $order = Order::sole();

        $this->assertSame('300000.00', $order->sub_total);
        $this->assertSame('100000.00', $order->discount_amount);
        $this->assertSame('230000.00', $order->total_amount);
        $this->assertSame(1, $coupon->refresh()->used_count);
        $this->assertTrue($userVoucher->refresh()->is_used);
    }

    public function test_checkout_stops_when_applied_voucher_was_deleted_before_order_creation(): void
    {
        Mail::fake();
        [$user, $addressId, $variant] = $this->checkoutFixture();
        [$coupon] = $this->voucherFixture($user, 'fixed');
        $couponCode = $coupon->code;

        $coupon->delete();

        $this->actingAs($user)
            ->from(route('client.checkout.index'))
            ->post(route('client.checkout.store'), [
                'user_address_id' => $addressId,
                'payment_method' => 'cod',
                'coupon_code' => $couponCode,
            ])
            ->assertRedirect(route('client.checkout.index'))
            ->assertSessionHas('error', function (string $message) use ($couponCode) {
                return str_contains($message, $couponCode)
                    && str_contains($message, 'đã không còn hiệu lực');
            });

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(0, $variant->refresh()->reserved_stock);
        $this->assertDatabaseHas('cart_items', [
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);
    }

    public function test_product_and_checkout_pages_show_the_shop_voucher_list_with_save_actions(): void
    {
        [$user, , $variant] = $this->checkoutFixture();

        $this->actingAs($user)
            ->get(route('client.checkout.index'))
            ->assertOk()
            ->assertSee('Voucher của Shop')
            ->assertSee('saveCheckoutVoucher', false)
            ->assertDontSee('filter(v => v.is_saved)', false);

        $this->actingAs($user)
            ->get(route('client.products.show', $variant->product->slug))
            ->assertOk()
            ->assertSee('Voucher của Shop')
            ->assertSee('saveVoucher', false);
    }

    /** @return array{User, int, ProductVariant} */
    private function checkoutFixture(): array
    {
        $suffix = Str::lower(Str::random(8));
        $user = User::factory()->create(['status' => true]);
        $address = $user->addresses()->create([
            'receiver_name' => $user->name,
            'receiver_phone' => '0901234567',
            'receiver_email' => $user->email,
            'address_detail' => '123 Nguyễn Huệ, Thành phố Hồ Chí Minh',
            'is_default' => true,
        ]);
        $category = Category::create([
            'name' => 'Danh mục voucher '.$suffix,
            'slug' => 'danh-muc-voucher-'.$suffix,
            'status' => true,
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Sản phẩm voucher '.$suffix,
            'slug' => 'san-pham-voucher-'.$suffix,
            'status' => true,
            'base_price' => 300000,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Size M',
            'base_price' => 300000,
            'stock' => 10,
        ]);
        $cart = Cart::create(['user_id' => $user->id]);
        $cart->items()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        return [$user, $address->id, $variant];
    }

    /** @return array{Coupon, UserVoucher} */
    private function voucherFixture(User $user, string $discountType): array
    {
        $coupon = Coupon::create([
            'code' => Str::upper($discountType.'-'.Str::random(10)),
            'description' => 'Voucher kiểm thử giới hạn mỗi đơn',
            'discount_type' => $discountType,
            'discount_value' => match ($discountType) {
                'freeship' => 0,
                'percent' => 50,
                default => 100000,
            },
            'max_discount_amount' => $discountType === 'percent' ? 100000 : null,
            'min_order_value' => 0,
            'usage_limit' => 10,
            'used_count' => 0,
            'start_date' => null,
            'expires_at' => now()->addDay(),
            'status' => true,
            'is_public' => false,
        ]);
        $userVoucher = UserVoucher::create([
            'user_id' => $user->id,
            'coupon_id' => $coupon->id,
            'is_used' => false,
            'used_at' => null,
        ]);

        return [$coupon, $userVoucher];
    }
}
