<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCouponMaxDiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_percentage_voucher_form_contains_conditional_maximum_discount_field(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => true]);

        $this->actingAs($admin)
            ->get(route('admin.coupons.create'))
            ->assertOk()
            ->assertSee('Số tiền giảm tối đa (VNĐ)')
            ->assertSee('id="max_discount_amount_container"', false)
            ->assertSee("discountTypeSelect.value === 'percent'", false);
    }

    public function test_percentage_voucher_requires_and_saves_maximum_discount_amount(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => true]);
        $payload = [
            'code' => 'GIAM50',
            'description' => 'Giảm 50% tối đa 100.000đ',
            'discount_type' => 'percent',
            'discount_value' => 50,
            'min_order_value' => 0,
            'usage_limit' => 100,
            'status' => 1,
        ];

        $this->actingAs($admin)
            ->post(route('admin.coupons.store'), $payload)
            ->assertSessionHasErrors('max_discount_amount');

        $this->assertDatabaseMissing('coupons', ['code' => 'GIAM50']);

        $this->actingAs($admin)
            ->post(route('admin.coupons.store'), $payload + [
                'max_discount_amount' => 100000,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.coupons.index'));

        $coupon = Coupon::where('code', 'GIAM50')->sole();

        $this->assertSame('50.00', $coupon->discount_value);
        $this->assertSame('100000.00', $coupon->max_discount_amount);
    }

    public function test_changing_percentage_voucher_to_fixed_clears_maximum_discount_amount(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => true]);
        $coupon = Coupon::create([
            'code' => 'DOILOAI50',
            'description' => 'Voucher đổi loại',
            'discount_type' => 'percent',
            'discount_value' => 50,
            'max_discount_amount' => 100000,
            'min_order_value' => 0,
            'usage_limit' => 10,
            'used_count' => 0,
            'status' => true,
            'is_public' => true,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.coupons.update', $coupon), [
                'code' => $coupon->code,
                'description' => $coupon->description,
                'discount_type' => 'fixed',
                'discount_value' => 50000,
                'min_order_value' => 0,
                'usage_limit' => 10,
                'status' => 1,
            ])
            ->assertSessionHasNoErrors();

        $this->assertNull($coupon->refresh()->max_discount_amount);
    }
}
