<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_review_each_product_in_a_completed_order_independently(): void
    {
        [$customer, $order, $details, $products] = $this->completedOrderWithTwoProducts();

        $this->actingAs($customer)
            ->post(route('client.orders.reviews.store', [$order, $details[0]]), [
                'rating' => 5,
                'content' => 'Áo đẹp, đúng kích thước và chất liệu tốt.',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->actingAs($customer)
            ->post(route('client.orders.reviews.store', [$order, $details[1]]), [
                'rating' => 4,
                'content' => 'Bóng có độ nảy tốt.',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertDatabaseCount('reviews', 2);
        $this->assertDatabaseHas('reviews', [
            'user_id' => $customer->id,
            'order_id' => $order->id,
            'order_detail_id' => $details[0]->id,
            'product_id' => $products[0]->id,
            'rating' => 5,
        ]);
        $this->assertDatabaseHas('reviews', [
            'user_id' => $customer->id,
            'order_id' => $order->id,
            'order_detail_id' => $details[1]->id,
            'product_id' => $products[1]->id,
            'rating' => 4,
        ]);
    }

    public function test_customer_receives_a_private_lifetime_freeship_voucher_after_review(): void
    {
        [$customer, $order, $details] = $this->completedOrderWithTwoProducts();

        $this->actingAs($customer)
            ->post(route('client.orders.reviews.store', [$order, $details[0]]), [
                'rating' => 5,
                'content' => 'Sản phẩm tốt, tôi rất hài lòng.',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', function (string $message): bool {
                return str_contains($message, 'voucher freeship dùng 1 lần, không thời hạn');
            });

        $coupon = Coupon::sole();

        $this->assertStringStartsWith('REVIEW-', $coupon->code);
        $this->assertSame('freeship', $coupon->discount_type);
        $this->assertSame(1, $coupon->usage_limit);
        $this->assertSame(0, $coupon->used_count);
        $this->assertNull($coupon->start_date);
        $this->assertNull($coupon->expires_at);
        $this->assertTrue($coupon->status);
        $this->assertFalse($coupon->is_public);
        $this->assertDatabaseHas('user_vouchers', [
            'user_id' => $customer->id,
            'coupon_id' => $coupon->id,
            'is_used' => false,
        ]);

        $this->getJson(route('vouchers.active'))
            ->assertOk()
            ->assertJsonPath('vouchers.0.code', $coupon->code)
            ->assertJsonPath('vouchers.0.discount_type', 'freeship')
            ->assertJsonPath('vouchers.0.expires_at', 'Không giới hạn');
    }

    public function test_review_reward_voucher_cannot_be_seen_or_used_by_another_customer(): void
    {
        [$customer, $order, $details] = $this->completedOrderWithTwoProducts();

        $this->actingAs($customer)
            ->post(route('client.orders.reviews.store', [$order, $details[0]]), [
                'rating' => 5,
            ])
            ->assertSessionHas('success');

        $coupon = Coupon::sole();
        $otherCustomer = User::factory()->create();

        $this->actingAs($otherCustomer)
            ->getJson(route('vouchers.active'))
            ->assertOk()
            ->assertJsonMissing(['code' => $coupon->code]);

        $this->postJson(route('vouchers.validate'), ['code' => $coupon->code])
            ->assertOk()
            ->assertJson([
                'success' => false,
                'message' => 'Mã giảm giá không khả dụng.',
            ]);
    }

    public function test_review_reward_voucher_can_only_be_used_once(): void
    {
        [$customer, $order, $details] = $this->completedOrderWithTwoProducts();

        $this->actingAs($customer)
            ->post(route('client.orders.reviews.store', [$order, $details[0]]), [
                'rating' => 5,
            ])
            ->assertSessionHas('success');

        $coupon = Coupon::sole();
        $userVoucher = $customer->userVouchers()->where('coupon_id', $coupon->id)->sole();

        $this->postJson(route('vouchers.validate'), ['code' => $coupon->code])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('coupon.discount_type', 'freeship');

        $userVoucher->update([
            'is_used' => true,
            'used_at' => now(),
        ]);
        $coupon->update(['used_count' => 1]);

        $this->getJson(route('vouchers.active'))
            ->assertOk()
            ->assertJsonPath('vouchers.0.code', $coupon->code)
            ->assertJsonPath('vouchers.0.is_used', true)
            ->assertJsonPath('vouchers.0.is_exhausted', true)
            ->assertJsonPath('vouchers.0.is_available', false);

        $this->postJson(route('vouchers.validate'), ['code' => $coupon->code])
            ->assertOk()
            ->assertJsonPath('success', false);
    }

    public function test_customer_cannot_review_before_order_is_completed(): void
    {
        [$customer, $order, $details] = $this->completedOrderWithTwoProducts();
        $order->update(['order_status' => 'shipping']);

        $this->actingAs($customer)
            ->post(route('client.orders.reviews.store', [$order, $details[0]]), [
                'rating' => 5,
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_customer_cannot_review_another_customers_order(): void
    {
        [, $order, $details] = $this->completedOrderWithTwoProducts();
        $otherCustomer = User::factory()->create();

        $this->actingAs($otherCustomer)
            ->post(route('client.orders.reviews.store', [$order, $details[0]]), [
                'rating' => 5,
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_customer_cannot_review_the_same_order_detail_twice(): void
    {
        [$customer, $order, $details] = $this->completedOrderWithTwoProducts();

        $this->actingAs($customer)
            ->post(route('client.orders.reviews.store', [$order, $details[0]]), [
                'rating' => 5,
            ])
            ->assertSessionHas('success');

        $this->actingAs($customer)
            ->post(route('client.orders.reviews.store', [$order, $details[0]]), [
                'rating' => 2,
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('reviews', 1);
        $this->assertDatabaseCount('coupons', 1);
        $this->assertDatabaseCount('user_vouchers', 1);
        $this->assertDatabaseHas('reviews', [
            'order_detail_id' => $details[0]->id,
            'rating' => 5,
        ]);
    }

    public function test_customer_can_attach_valid_images_and_videos_to_a_review(): void
    {
        Storage::fake('public');
        [$customer, $order, $details] = $this->completedOrderWithTwoProducts();

        $this->actingAs($customer)
            ->post(route('client.orders.reviews.store', [$order, $details[0]]), [
                'rating' => 5,
                'media' => [
                    UploadedFile::fake()->image('san-pham-thuc-te.jpg')->size(1024),
                    UploadedFile::fake()->create('mo-hop.mp4', 1024, 'video/mp4'),
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $review = Review::with('media')->sole();

        $this->assertCount(2, $review->media);
        $this->assertSame(['image', 'video'], $review->media->pluck('type')->all());

        foreach ($review->media as $media) {
            Storage::disk('public')->assertExists($media->path);
        }
    }

    public function test_review_rejects_unsupported_media_format(): void
    {
        Storage::fake('public');
        [$customer, $order, $details] = $this->completedOrderWithTwoProducts();

        $this->actingAs($customer)
            ->post(route('client.orders.reviews.store', [$order, $details[0]]), [
                'rating' => 5,
                'media' => [
                    UploadedFile::fake()->create('tai-lieu.pdf', 100, 'application/pdf'),
                ],
            ])
            ->assertSessionHasErrors('media.0');

        $this->assertDatabaseCount('reviews', 0);
        $this->assertDatabaseCount('review_media', 0);
    }

    public function test_review_rejects_more_than_five_media_files(): void
    {
        Storage::fake('public');
        [$customer, $order, $details] = $this->completedOrderWithTwoProducts();

        $media = [];

        for ($index = 1; $index <= 6; $index++) {
            $media[] = UploadedFile::fake()->image("anh-{$index}.jpg");
        }

        $this->actingAs($customer)
            ->post(route('client.orders.reviews.store', [$order, $details[0]]), [
                'rating' => 5,
                'media' => $media,
            ])
            ->assertSessionHasErrors('media');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_review_enforces_separate_image_and_video_size_limits(): void
    {
        Storage::fake('public');
        [$customer, $order, $details] = $this->completedOrderWithTwoProducts();

        $this->actingAs($customer)
            ->post(route('client.orders.reviews.store', [$order, $details[0]]), [
                'rating' => 5,
                'media' => [
                    UploadedFile::fake()->image('anh-qua-lon.jpg')->size(5121),
                ],
            ])
            ->assertSessionHasErrors('media.0');

        $this->actingAs($customer)
            ->post(route('client.orders.reviews.store', [$order, $details[0]]), [
                'rating' => 5,
                'media' => [
                    UploadedFile::fake()->create('video-qua-lon.mp4', 51201, 'video/mp4'),
                ],
            ])
            ->assertSessionHasErrors('media.0');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_completed_order_pages_show_a_separate_review_action_for_each_product(): void
    {
        [$customer, $order, $details] = $this->completedOrderWithTwoProducts();

        $response = $this->actingAs($customer)
            ->get(route('account.show').'#orders')
            ->assertOk();

        foreach ($details as $detail) {
            $response->assertSee(
                route('client.orders.reviews.store', [$order, $detail]),
                false
            );
        }

        $response
            ->assertSee('data-product-review', false)
            ->assertSee('Đánh giá sản phẩm');
    }

    public function test_product_page_displays_verified_purchase_review_and_rating_summary(): void
    {
        Storage::fake('public');
        [$customer, $order, $details, $products] = $this->completedOrderWithTwoProducts();

        $review = Review::create([
            'user_id' => $customer->id,
            'product_id' => $products[0]->id,
            'order_id' => $order->id,
            'order_detail_id' => $details[0]->id,
            'rating' => 5,
            'content' => 'Sản phẩm rất đáng mua.',
        ]);

        Storage::disk('public')->put('reviews/minh-chung.jpg', 'image-content');
        $review->media()->create([
            'type' => 'image',
            'path' => 'reviews/minh-chung.jpg',
            'mime_type' => 'image/jpeg',
            'original_name' => 'minh-chung.jpg',
            'size' => 13,
        ]);

        $this->get(route('client.products.show', $products[0]->slug))
            ->assertOk()
            ->assertSee('5.0/5')
            ->assertSee('1 đánh giá')
            ->assertSee('Đã mua hàng')
            ->assertSee('Sản phẩm rất đáng mua.')
            ->assertSee('/storage/reviews/minh-chung.jpg', false)
            ->assertSee($customer->name);
    }

    /**
     * @return array{User, Order, array{OrderDetail, OrderDetail}, array{Product, Product}}
     */
    private function completedOrderWithTwoProducts(): array
    {
        $customer = User::factory()->create();
        $suffix = Str::lower(Str::random(8));

        $category = Category::create([
            'name' => 'Danh mục đánh giá',
            'slug' => "danh-muc-danh-gia-{$suffix}",
            'status' => true,
        ]);

        $firstProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'Áo bóng đá đánh giá',
            'slug' => "ao-bong-da-danh-gia-{$suffix}",
            'status' => true,
            'base_price' => 250000,
        ]);

        $secondProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'Bóng thi đấu đánh giá',
            'slug' => "bong-thi-dau-danh-gia-{$suffix}",
            'status' => true,
            'base_price' => 400000,
        ]);

        $firstVariant = ProductVariant::create([
            'product_id' => $firstProduct->id,
            'name' => 'Size L',
            'sku' => "AO-{$suffix}",
            'base_price' => 250000,
            'stock' => 10,
        ]);

        $secondVariant = ProductVariant::create([
            'product_id' => $secondProduct->id,
            'name' => 'Size 5',
            'sku' => "BONG-{$suffix}",
            'base_price' => 400000,
            'stock' => 10,
        ]);

        $order = Order::create([
            'user_id' => $customer->id,
            'order_code' => strtoupper(Str::random(10)),
            'customer_name' => $customer->name,
            'customer_phone' => '0901234567',
            'customer_email' => $customer->email,
            'customer_address' => '123 Nguyễn Huệ, Thành phố Hồ Chí Minh',
            'sub_total' => 900000,
            'shipping_fee' => 30000,
            'discount_amount' => 0,
            'total_amount' => 930000,
            'payment_method' => 'cod',
            'payment_status' => 'paid',
            'order_status' => 'completed',
        ]);

        $firstDetail = OrderDetail::create([
            'order_id' => $order->id,
            'product_variant_id' => $firstVariant->id,
            'quantity' => 2,
            'price' => 250000,
        ]);

        $secondDetail = OrderDetail::create([
            'order_id' => $order->id,
            'product_variant_id' => $secondVariant->id,
            'quantity' => 1,
            'price' => 400000,
        ]);

        return [
            $customer,
            $order,
            [$firstDetail, $secondDetail],
            [$firstProduct, $secondProduct],
        ];
    }
}
