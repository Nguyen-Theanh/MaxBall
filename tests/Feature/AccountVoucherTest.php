<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\User;
use App\Models\UserVoucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountVoucherTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_page_displays_only_the_customers_vouchers_and_their_statuses(): void
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();

        $availableCoupon = $this->coupon([
            'code' => 'REVIEW-MY-FREESHIP',
            'description' => 'Quà tặng freeship sau khi đánh giá sản phẩm',
            'discount_type' => 'freeship',
            'usage_limit' => 1,
            'is_public' => false,
        ]);
        $usedCoupon = $this->coupon([
            'code' => 'WELCOME-USED',
            'discount_type' => 'fixed',
            'discount_value' => 50000,
            'usage_limit' => 10,
            'used_count' => 1,
        ]);
        $otherCoupon = $this->coupon([
            'code' => 'OTHER-PRIVATE-VOUCHER',
            'discount_type' => 'percent',
            'discount_value' => 10,
            'is_public' => false,
        ]);

        UserVoucher::create([
            'user_id' => $customer->id,
            'coupon_id' => $availableCoupon->id,
            'is_used' => false,
        ]);
        UserVoucher::create([
            'user_id' => $customer->id,
            'coupon_id' => $usedCoupon->id,
            'is_used' => true,
            'used_at' => now(),
        ]);
        UserVoucher::create([
            'user_id' => $otherCustomer->id,
            'coupon_id' => $otherCoupon->id,
            'is_used' => false,
        ]);

        $response = $this->actingAs($customer)
            ->get(route('account.show').'#vouchers')
            ->assertOk()
            ->assertSee('Voucher Của Tôi')
            ->assertSee('voucher có thể sử dụng')
            ->assertSee('REVIEW-MY-FREESHIP')
            ->assertSee('Miễn phí vận chuyển')
            ->assertSee('Không thời hạn')
            ->assertSee('Có thể sử dụng')
            ->assertSee('WELCOME-USED')
            ->assertSee('Đã sử dụng')
            ->assertDontSee('OTHER-PRIVATE-VOUCHER');

        $this->assertSame(1, $response->viewData('activeVoucherCount'));
        $this->assertSame(2, $response->viewData('userVouchers')->total());
    }

    /** @param array<string, mixed> $overrides */
    private function coupon(array $overrides): Coupon
    {
        return Coupon::create(array_merge([
            'code' => 'VOUCHER-'.fake()->unique()->numerify('######'),
            'description' => null,
            'discount_type' => 'fixed',
            'discount_value' => 0,
            'min_order_value' => 0,
            'usage_limit' => null,
            'used_count' => 0,
            'start_date' => null,
            'expires_at' => null,
            'status' => true,
            'is_public' => true,
        ], $overrides));
    }
}
