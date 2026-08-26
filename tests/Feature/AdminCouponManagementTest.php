<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use App\Models\UserVoucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCouponManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_vouchers_by_source_and_see_used_over_total(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => true]);

        $this->coupon([
            'code' => 'ADMIN-VOUCHER',
            'usage_limit' => 10,
            'used_count' => 3,
            'is_public' => true,
        ]);
        $this->coupon([
            'code' => 'CUSTOMER-VOUCHER',
            'usage_limit' => 1,
            'is_public' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.coupons.index'))
            ->assertOk()
            ->assertSee('Nguồn voucher')
            ->assertSee('Voucher admin')
            ->assertSee('Voucher khách hàng')
            ->assertSee('>Đã dùng</th>', false)
            ->assertSee('3 / 10')
            ->assertSee('ADMIN-VOUCHER')
            ->assertSee('CUSTOMER-VOUCHER');

        $this->actingAs($admin)
            ->get(route('admin.coupons.index', ['source' => 'admin']))
            ->assertOk()
            ->assertSee('ADMIN-VOUCHER')
            ->assertDontSee('CUSTOMER-VOUCHER');

        $this->actingAs($admin)
            ->get(route('admin.coupons.index', ['source' => 'customer']))
            ->assertOk()
            ->assertSee('CUSTOMER-VOUCHER')
            ->assertDontSee('ADMIN-VOUCHER');
    }

    public function test_vouchers_created_by_admin_are_marked_as_admin_vouchers(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => true]);

        $this->actingAs($admin)
            ->post(route('admin.coupons.store'), [
                'code' => 'CREATED-BY-ADMIN',
                'discount_type' => 'fixed',
                'discount_value' => 10000,
                'usage_limit' => 20,
                'status' => 1,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.coupons.index'));

        $this->assertDatabaseHas('coupons', [
            'code' => 'CREATED-BY-ADMIN',
            'is_public' => true,
        ]);
    }

    public function test_admin_coupon_list_defaults_to_active_and_can_filter_inactive_vouchers(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => true]);

        $this->coupon(['code' => 'ACTIVE-VOUCHER']);
        $this->coupon([
            'code' => 'EXHAUSTED-VOUCHER',
            'usage_limit' => 2,
            'used_count' => 2,
        ]);
        $expiredVoucher = $this->coupon([
            'code' => 'EXPIRED-VOUCHER',
            'expires_at' => now()->subMinute(),
        ]);
        $this->coupon([
            'code' => 'DISABLED-VOUCHER',
            'status' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.coupons.index'))
            ->assertOk()
            ->assertSee('Tình trạng sử dụng')
            ->assertSee('toggle-status', false)
            ->assertSee('ACTIVE-VOUCHER')
            ->assertDontSee('EXHAUSTED-VOUCHER')
            ->assertDontSee('EXPIRED-VOUCHER')
            ->assertDontSee('DISABLED-VOUCHER');

        $this->actingAs($admin)
            ->get(route('admin.coupons.index', ['availability' => 'inactive']))
            ->assertOk()
            ->assertDontSee('ACTIVE-VOUCHER')
            ->assertSee('EXHAUSTED-VOUCHER')
            ->assertSee('Đã hết lượt')
            ->assertSee('EXPIRED-VOUCHER')
            ->assertSee('Đã hết hạn')
            ->assertSee('DISABLED-VOUCHER')
            ->assertSee('Đã tắt')
            ->assertDontSee('action="'.route('admin.coupons.toggle-status', $expiredVoucher).'"', false);
    }

    public function test_vouchers_used_by_orders_cannot_be_edited_or_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => true]);
        $customer = User::factory()->create(['status' => true]);
        $discountCoupon = $this->coupon(['code' => 'ORDER-DISCOUNT']);
        $freeshipCoupon = $this->coupon([
            'code' => 'ORDER-FREESHIP',
            'discount_type' => 'freeship',
            'discount_value' => 0,
        ]);
        $editableCoupon = $this->coupon(['code' => 'NO-ORDER-YET']);

        $this->order($customer, $discountCoupon);
        $this->order($customer, null, $freeshipCoupon);

        $response = $this->actingAs($admin)
            ->get(route('admin.coupons.index'))
            ->assertOk()
            ->assertSee('Đã phát sinh đơn')
            ->assertDontSee(route('admin.coupons.edit', $discountCoupon), false)
            ->assertDontSee(route('admin.coupons.edit', $freeshipCoupon), false)
            ->assertDontSee('action="'.route('admin.coupons.destroy', $discountCoupon).'"', false)
            ->assertDontSee('action="'.route('admin.coupons.destroy', $freeshipCoupon).'"', false)
            ->assertSee(route('admin.coupons.edit', $editableCoupon), false);

        $this->actingAs($admin)
            ->get(route('admin.coupons.edit', $discountCoupon))
            ->assertRedirect(route('admin.coupons.index'))
            ->assertSessionHas('error', 'Voucher đã phát sinh đơn hàng nên không thể sửa.');

        $this->actingAs($admin)
            ->put(route('admin.coupons.update', $discountCoupon), [
                'code' => $discountCoupon->code,
                'discount_type' => 'fixed',
                'discount_value' => 20000,
                'status' => 1,
            ])
            ->assertRedirect(route('admin.coupons.index'))
            ->assertSessionHas('error', 'Voucher đã phát sinh đơn hàng nên không thể sửa.');

        $this->assertSame('10000.00', $discountCoupon->refresh()->discount_value);

        $this->actingAs($admin)
            ->delete(route('admin.coupons.destroy', $discountCoupon))
            ->assertRedirect(route('admin.coupons.index'))
            ->assertSessionHas('error', 'Voucher đã phát sinh đơn hàng nên không thể xóa.');

        $this->assertModelExists($discountCoupon);
    }

    public function test_expired_coupon_command_deletes_only_expired_vouchers(): void
    {
        $customer = User::factory()->create();
        $expiredPublic = $this->coupon([
            'code' => 'EXPIRED-PUBLIC',
            'expires_at' => now()->subMinute(),
        ]);
        $expiredCustomer = $this->coupon([
            'code' => 'EXPIRED-CUSTOMER',
            'expires_at' => now()->subHour(),
            'is_public' => false,
        ]);
        $futureCoupon = $this->coupon([
            'code' => 'FUTURE-VOUCHER',
            'expires_at' => now()->addDay(),
        ]);
        $permanentCoupon = $this->coupon([
            'code' => 'PERMANENT-VOUCHER',
            'expires_at' => null,
        ]);
        $expiredCouponWithOrder = $this->coupon([
            'code' => 'EXPIRED-WITH-ORDER',
            'expires_at' => now()->subDay(),
        ]);
        $this->order($customer, $expiredCouponWithOrder);
        $userVoucher = UserVoucher::create([
            'user_id' => $customer->id,
            'coupon_id' => $expiredCustomer->id,
            'is_used' => false,
        ]);

        $this->artisan('coupons:delete-expired')
            ->expectsOutput('Đã xóa 2 voucher hết hạn.')
            ->assertSuccessful();

        $this->assertModelMissing($expiredPublic);
        $this->assertModelMissing($expiredCustomer);
        $this->assertModelExists($futureCoupon);
        $this->assertModelExists($permanentCoupon);
        $this->assertModelExists($expiredCouponWithOrder);
        $this->assertModelMissing($userVoucher);
    }

    public function test_active_voucher_api_keeps_exhausted_voucher_visible_but_unavailable(): void
    {
        $customer = User::factory()->create();
        $exhausted = $this->coupon([
            'code' => 'NO-USAGE-LEFT',
            'usage_limit' => 2,
            'used_count' => 2,
        ]);
        $available = $this->coupon([
            'code' => 'STILL-AVAILABLE',
            'usage_limit' => 2,
            'used_count' => 1,
        ]);
        $this->coupon([
            'code' => 'ALREADY-EXPIRED',
            'expires_at' => now()->subMinute(),
        ]);

        UserVoucher::create([
            'user_id' => $customer->id,
            'coupon_id' => $exhausted->id,
            'is_used' => false,
        ]);

        $response = $this->actingAs($customer)
            ->getJson(route('vouchers.active'))
            ->assertOk()
            ->assertJsonMissing(['code' => 'ALREADY-EXPIRED']);

        $vouchers = collect($response->json('vouchers'))->keyBy('code');

        $this->assertTrue($vouchers->get('NO-USAGE-LEFT')['is_exhausted']);
        $this->assertFalse($vouchers->get('NO-USAGE-LEFT')['is_available']);
        $this->assertTrue($vouchers->get('NO-USAGE-LEFT')['is_saved']);
        $this->assertFalse($vouchers->get('STILL-AVAILABLE')['is_exhausted']);
        $this->assertTrue($vouchers->get('STILL-AVAILABLE')['is_available']);
        $this->assertFalse($vouchers->get('STILL-AVAILABLE')['is_saved']);
        $this->assertSame($available->id, $vouchers->get('STILL-AVAILABLE')['id']);
    }

    /** @param array<string, mixed> $overrides */
    private function coupon(array $overrides): Coupon
    {
        return Coupon::create(array_merge([
            'code' => 'VOUCHER-'.fake()->unique()->numerify('######'),
            'description' => null,
            'discount_type' => 'fixed',
            'discount_value' => 10000,
            'max_discount_amount' => null,
            'min_order_value' => 0,
            'usage_limit' => null,
            'used_count' => 0,
            'start_date' => null,
            'expires_at' => null,
            'status' => true,
            'is_public' => true,
        ], $overrides));
    }

    private function order(
        User $customer,
        ?Coupon $coupon = null,
        ?Coupon $freeshipCoupon = null
    ): Order {
        return Order::create([
            'user_id' => $customer->id,
            'coupon_id' => $coupon?->id,
            'freeship_coupon_id' => $freeshipCoupon?->id,
            'order_code' => fake()->unique()->bothify('ORDER-########'),
            'customer_name' => $customer->name,
            'customer_phone' => '0901234567',
            'customer_email' => $customer->email,
            'customer_address' => '123 Nguyễn Huệ, Thành phố Hồ Chí Minh',
            'sub_total' => 200000,
            'shipping_fee' => $freeshipCoupon ? 0 : 30000,
            'discount_amount' => $coupon ? 10000 : 0,
            'total_amount' => $coupon ? 220000 : 200000,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'order_status' => 'pending',
        ]);
    }
}
