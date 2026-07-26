<?php

namespace Tests\Feature;

use App\Mail\OrderCreatedMail;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckoutEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_sends_confirmation_to_the_shipping_address_email(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'account@example.com',
        ]);

        $address = $user->addresses()->create([
            'receiver_name' => 'Nguyễn Văn An',
            'receiver_phone' => '0901234567',
            'receiver_email' => 'shipping@example.com',
            'address_line' => '123 Nguyễn Huệ',
            'province_code' => 79,
            'province_name' => 'Thành phố Hồ Chí Minh',
            'ward_code' => 26734,
            'ward_name' => 'Phường Sài Gòn',
            'address_detail' => '123 Nguyễn Huệ, Phường Sài Gòn, Thành phố Hồ Chí Minh',
            'is_default' => true,
        ]);

        $category = Category::create([
            'name' => 'Áo bóng đá',
            'slug' => 'ao-bong-da-checkout-email',
            'status' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Áo kiểm thử email',
            'slug' => 'ao-kiem-thu-email',
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
            ->post(route('client.checkout.store'), [
                'user_address_id' => $address->id,
                'payment_method' => 'cod',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('client.orders.index'));

        $order = Order::firstOrFail();

        $this->assertSame('shipping@example.com', $order->customer_email);

        Mail::assertSent(
            OrderCreatedMail::class,
            fn (OrderCreatedMail $mail): bool => $mail->hasTo('shipping@example.com')
                && $mail->order->is($order)
        );

        $renderedMail = (new OrderCreatedMail(
            $order->load('details.variant.product')
        ))->render();

        $this->assertStringContainsString('Kính gửi Nguyễn Văn An', $renderedMail);
        $this->assertStringContainsString('Cảm ơn bạn đã tin tưởng và lựa chọn mua sắm tại', $renderedMail);
        $this->assertStringContainsString('#'.$order->order_code, $renderedMail);
        $this->assertStringContainsString('230.000đ', $renderedMail);
        $this->assertStringContainsString('support@maxball.com', $renderedMail);
        $this->assertStringContainsString('0123 456 789', $renderedMail);
        $this->assertStringContainsString('Đội ngũ MaxBall', $renderedMail);
    }
}
