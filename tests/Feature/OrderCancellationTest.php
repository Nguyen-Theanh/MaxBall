<?php

namespace Tests\Feature;

use App\Mail\OrderCancelledMail;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderCancellationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_cancel_with_a_reason_and_stock_is_restored(): void
    {
        Mail::fake();

        [$customer, , $order, $variant] = $this->orderFixture('processing');

        $this->actingAs($customer)
            ->put(route('client.orders.cancel', $order), [
                'cancellation_reason' => 'change_variant_or_quantity',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $order->refresh();

        $this->assertSame('cancelled', $order->order_status);
        $this->assertSame('customer', $order->cancelled_by);
        $this->assertSame('change_variant_or_quantity', $order->cancellation_reason);
        $this->assertSame('Muốn thay đổi size/màu/số lượng.', $order->cancellation_reason_label);
        $this->assertNull($order->cancellation_note);
        $this->assertNotNull($order->cancelled_at);
        $this->assertSame(10, $variant->refresh()->stock);

        Mail::assertSent(
            OrderCancelledMail::class,
            fn (OrderCancelledMail $mail): bool => $mail->hasTo($order->customer_email)
                && $mail->order->is($order)
        );

        $renderedMail = (new OrderCancelledMail($order))->render();
        $this->assertStringContainsString(
            'Theo yêu cầu của bạn, đơn hàng đã được hủy thành công.',
            $renderedMail
        );
        $this->assertStringContainsString('Muốn thay đổi size/màu/số lượng.', $renderedMail);
        $this->assertStringContainsString('không phát sinh yêu cầu hoàn tiền', $renderedMail);
    }

    public function test_customer_must_enter_a_note_for_other_reason(): void
    {
        Mail::fake();

        [$customer, , $order, $variant] = $this->orderFixture();

        $this->actingAs($customer)
            ->put(route('client.orders.cancel', $order), [
                'cancellation_reason' => 'other',
            ])
            ->assertSessionHasErrors('cancellation_note');

        $this->assertSame('pending', $order->refresh()->order_status);
        $this->assertSame(8, $variant->refresh()->stock);
        Mail::assertNothingSent();

        $this->actingAs($customer)
            ->put(route('client.orders.cancel', $order), [
                'cancellation_reason' => 'other',
                'cancellation_note' => 'Tôi cần đặt lại đơn với thông tin người nhận khác.',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(
            'Tôi cần đặt lại đơn với thông tin người nhận khác.',
            $order->refresh()->cancellation_note
        );
        Mail::assertSent(OrderCancelledMail::class);
    }

    public function test_admin_can_cancel_with_an_admin_reason(): void
    {
        Mail::fake();

        [, $admin, $order, $variant] = $this->orderFixture('processing');
        $order->update(['payment_status' => 'paid']);

        $this->actingAs($admin)
            ->patch(route('admin.orders.updateStatus', $order), [
                'order_status' => 'cancelled',
                'cancellation_reason' => 'out_of_stock',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $order->refresh();

        $this->assertSame('cancelled', $order->order_status);
        $this->assertSame('admin', $order->cancelled_by);
        $this->assertSame('out_of_stock', $order->cancellation_reason);
        $this->assertSame('Sản phẩm đã hết hàng.', $order->cancellation_reason_label);
        $this->assertNotNull($order->cancelled_at);
        $this->assertSame(10, $variant->refresh()->stock);

        Mail::assertSent(
            OrderCancelledMail::class,
            fn (OrderCancelledMail $mail): bool => $mail->hasTo($order->customer_email)
                && $mail->order->is($order)
        );

        $renderedMail = (new OrderCancelledMail($order))->render();
        $this->assertStringContainsString(
            'sản phẩm trong đơn hàng hiện đã hết hàng',
            $renderedMail
        );
        $this->assertStringContainsString('tiến hành hoàn tiền theo phương thức thanh toán ban đầu', $renderedMail);
    }

    public function test_admin_cancellation_email_explains_when_customer_cannot_be_contacted(): void
    {
        Mail::fake();

        [, $admin, $order] = $this->orderFixture('processing');

        $this->actingAs($admin)
            ->patch(route('admin.orders.updateStatus', $order), [
                'order_status' => 'cancelled',
                'cancellation_reason' => 'cannot_contact_customer',
            ])
            ->assertSessionHasNoErrors();

        $renderedMail = (new OrderCancelledMail($order->refresh()))->render();

        $this->assertStringContainsString(
            'Chúng tôi đã nhiều lần cố gắng liên hệ để xác nhận đơn hàng nhưng không thành công',
            $renderedMail
        );
        Mail::assertSent(OrderCancelledMail::class);
    }

    public function test_customer_and_admin_see_their_respective_reason_lists(): void
    {
        [$customer, $admin, $order] = $this->orderFixture();

        $this->actingAs($customer)
            ->get(route('client.orders.show', $order))
            ->assertOk()
            ->assertSee('Thay đổi nhu cầu, không muốn mua nữa.')
            ->assertSee('Muốn thay đổi size/màu/số lượng.')
            ->assertDontSee('Sản phẩm đã hết hàng.');

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Sản phẩm đã hết hàng.')
            ->assertSee('Đơn hàng bất thường hoặc nghi ngờ gian lận.')
            ->assertDontSee('Tìm được nơi bán giá tốt hơn.');
    }

    public function test_account_orders_tab_shows_detail_and_cancel_actions(): void
    {
        [$customer, , $order] = $this->orderFixture();

        $this->actingAs($customer)
            ->get(route('account.show').'#orders')
            ->assertOk()
            ->assertSee(route('client.orders.show', $order), false)
            ->assertSee(route('client.orders.cancel', $order), false)
            ->assertSee('data-customer-cancel', false)
            ->assertSee('Xem chi tiết')
            ->assertSee('Hủy đơn')
            ->assertSee('Thay đổi nhu cầu, không muốn mua nữa.');
    }

    /**
     * @return array{User, User, Order, ProductVariant}
     */
    private function orderFixture(string $status = 'pending'): array
    {
        $customer = User::factory()->create();
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => true,
        ]);
        $suffix = Str::lower(Str::random(8));

        $category = Category::create([
            'name' => 'Danh mục kiểm thử',
            'slug' => "danh-muc-{$suffix}",
            'status' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Sản phẩm kiểm thử hủy đơn',
            'slug' => "san-pham-{$suffix}",
            'status' => true,
            'base_price' => 200000,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Size M',
            'base_price' => 200000,
            'stock' => 8,
        ]);

        $order = Order::create([
            'user_id' => $customer->id,
            'order_code' => strtoupper(Str::random(10)),
            'customer_name' => $customer->name,
            'customer_phone' => '0901234567',
            'customer_email' => $customer->email,
            'customer_address' => '123 Nguyễn Huệ, Thành phố Hồ Chí Minh',
            'sub_total' => 400000,
            'shipping_fee' => 30000,
            'discount_amount' => 0,
            'total_amount' => 430000,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'order_status' => $status,
        ]);

        OrderDetail::create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'price' => 200000,
        ]);

        return [$customer, $admin, $order, $variant];
    }
}
