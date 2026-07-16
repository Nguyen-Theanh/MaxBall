<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VietnamAddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_load_post_merger_provinces_and_wards(): void
    {
        $this->fakeAddressApi();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson(route('api.vietnam-address.provinces'))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Thành phố Hà Nội')
            ->assertJsonPath('data.1.code', 79);

        $this->actingAs($user)
            ->getJson(route('api.vietnam-address.wards', ['province_code' => 79]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Phường Sài Gòn');
    }

    public function test_user_address_is_saved_and_updated_with_verified_administrative_units(): void
    {
        $this->fakeAddressApi();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('account.addresses.store'), [
                'receiver_name' => 'Nguyễn Văn An',
                'receiver_phone' => '0901234567',
                'address_line' => '123 Nguyễn Huệ',
                'province_code' => 79,
                'ward_code' => 26734,
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('user_addresses', [
            'user_id' => $user->id,
            'address_line' => '123 Nguyễn Huệ',
            'province_code' => 79,
            'province_name' => 'Thành phố Hồ Chí Minh',
            'ward_code' => 26734,
            'ward_name' => 'Phường Sài Gòn',
            'address_detail' => '123 Nguyễn Huệ, Phường Sài Gòn, Thành phố Hồ Chí Minh',
            'is_default' => true,
        ]);

        $address = $user->addresses()->firstOrFail();

        $this->actingAs($user)
            ->put(route('account.addresses.update', $address), [
                'receiver_name' => 'Nguyễn Văn An',
                'receiver_phone' => '0901234567',
                'address_line' => '456 Lê Lợi',
                'province_code' => 79,
                'ward_code' => 26734,
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('user_addresses', [
            'id' => $address->id,
            'address_line' => '456 Lê Lợi',
            'address_detail' => '456 Lê Lợi, Phường Sài Gòn, Thành phố Hồ Chí Minh',
        ]);
    }

    public function test_address_is_rejected_when_ward_does_not_belong_to_province(): void
    {
        $this->fakeAddressApi();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('account.show'))
            ->post(route('account.addresses.store'), [
                'receiver_name' => 'Nguyễn Văn An',
                'receiver_phone' => '0901234567',
                'address_line' => '123 Nguyễn Huệ',
                'province_code' => 79,
                'ward_code' => 99999,
            ])
            ->assertRedirect(route('account.show'))
            ->assertSessionHasErrors('ward_code');

        $this->assertDatabaseCount('user_addresses', 0);
    }

    public function test_profile_address_becomes_the_default_shipping_address_and_prefills_checkout(): void
    {
        $this->fakeAddressApi();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('account.update'), [
                'form_context' => 'profile',
                'name' => 'Nguyễn Văn An',
                'email' => $user->email,
                'phone' => '0901234567',
                'default_address' => [
                    'address_line' => '123 Nguyễn Huệ',
                    'province_code' => 79,
                    'ward_code' => 26734,
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $address = $user->addresses()->firstOrFail();

        $this->assertTrue($address->is_default);
        $this->assertSame('Nguyễn Văn An', $address->receiver_name);
        $this->assertSame('0901234567', $address->receiver_phone);
        $this->assertSame(
            '123 Nguyễn Huệ, Phường Sài Gòn, Thành phố Hồ Chí Minh',
            $address->address_detail
        );

        $this->actingAs($user)
            ->put(route('account.update'), [
                'form_context' => 'profile',
                'name' => 'Nguyễn Văn An Mới',
                'email' => $user->email,
                'phone' => '0912345678',
                'default_address' => [
                    'address_line' => '456 Lê Lợi',
                    'province_code' => 79,
                    'ward_code' => 26734,
                ],
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('user_addresses', 1);
        $this->assertDatabaseHas('user_addresses', [
            'id' => $address->id,
            'receiver_name' => 'Nguyễn Văn An Mới',
            'receiver_phone' => '0912345678',
            'address_detail' => '456 Lê Lợi, Phường Sài Gòn, Thành phố Hồ Chí Minh',
            'is_default' => true,
        ]);

        $category = Category::create([
            'name' => 'Áo bóng đá',
            'slug' => 'ao-bong-da-profile-address',
            'status' => true,
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Áo dùng địa chỉ mặc định',
            'slug' => 'ao-dung-dia-chi-mac-dinh',
            'status' => true,
            'base_price' => 200000,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Size M',
            'base_price' => 200000,
            'stock' => 10,
        ]);
        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('client.checkout.index'))
            ->assertOk()
            ->assertSee('Nguyễn Văn An Mới')
            ->assertSee('0912345678')
            ->assertSee('456 Lê Lợi, Phường Sài Gòn, Thành phố Hồ Chí Minh')
            ->assertSee('editSelectedCheckoutAddress()', false)
            ->assertSee('value="'.$address->id.'"', false);
    }

    public function test_account_and_checkout_render_the_shared_two_level_address_fields(): void
    {
        $user = User::factory()->create();
        $category = Category::create([
            'name' => 'Áo bóng đá',
            'slug' => 'ao-bong-da',
            'status' => true,
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Áo kiểm thử',
            'slug' => 'ao-kiem-thu',
            'status' => true,
            'base_price' => 200000,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Size M',
            'base_price' => 200000,
            'stock' => 10,
        ]);
        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('account.show'))
            ->assertOk()
            ->assertSee('profile-address_province_code', false)
            ->assertSee('profile-address_ward_code', false)
            ->assertSee('account-address_province_code', false)
            ->assertSee('account-address_ward_code', false);

        $this->actingAs($user)
            ->get(route('client.checkout.index'))
            ->assertOk()
            ->assertSee('checkout-address_province_code', false)
            ->assertSee('checkout-address_ward_code', false);
    }

    private function fakeAddressApi(): void
    {
        Http::fake([
            'https://provinces.open-api.vn/api/v2/p/' => Http::response([
                [
                    'name' => 'Thành phố Hà Nội',
                    'code' => 1,
                    'division_type' => 'thành phố trung ương',
                ],
                [
                    'name' => 'Thành phố Hồ Chí Minh',
                    'code' => 79,
                    'division_type' => 'thành phố trung ương',
                ],
            ]),
            'https://provinces.open-api.vn/api/v2/w/*' => Http::response([
                [
                    'name' => 'Phường Sài Gòn',
                    'code' => 26734,
                    'division_type' => 'phường',
                    'province_code' => 79,
                ],
            ]),
        ]);
    }
}
