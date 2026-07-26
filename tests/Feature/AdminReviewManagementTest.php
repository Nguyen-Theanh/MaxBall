<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminReviewManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_review_management_page(): void
    {
        [$admin, , $product] = $this->fixture();

        $this->actingAs($admin)
            ->get(route('admin.reviews.index'))
            ->assertOk()
            ->assertSee('Quản lý đánh giá')
            ->assertSee('Đăng đánh giá ẩn danh')
            ->assertSee($product->name)
            ->assertSee(route('admin.reviews.store'), false);
    }

    public function test_admin_can_publish_an_anonymous_brand_review_with_media(): void
    {
        Storage::fake('public');
        [$admin, , $product] = $this->fixture();

        $this->actingAs($admin)
            ->post(route('admin.reviews.store'), [
                'product_id' => $product->id,
                'rating' => 5,
                'content' => 'Mẫu áo được MaxBall đề xuất cho các trận đấu cuối tuần.',
                'is_visible' => 1,
                'public_name' => 'Nguyễn Minh Anh',
                'media' => [
                    UploadedFile::fake()->image('goi-y-tu-maxball.jpg')->size(1024),
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $review = Review::with('media')->sole();

        $this->assertTrue($review->is_admin_review);
        $this->assertTrue($review->is_visible);
        $this->assertSame($admin->id, $review->user_id);
        $this->assertCount(1, $review->media);
        Storage::disk('public')->assertExists($review->media->first()->path);

        $this->post(route('logout'))->assertRedirect();

        $this->get(route('client.products.show', $product->slug))
            ->assertOk()
            ->assertSee('Nguyễn Minh Anh')
            ->assertSee('Mẫu áo được MaxBall đề xuất cho các trận đấu cuối tuần.')
            ->assertDontSee('Đội ngũ MaxBall')
            ->assertDontSee('Đánh giá từ MaxBall')
            ->assertDontSee($admin->name)
            ->assertDontSee($admin->email);
    }

    public function test_admin_can_hide_and_restore_a_review(): void
    {
        [$admin, $customer, $product] = $this->fixture();
        $review = Review::create([
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'rating' => 4,
            'content' => 'Nội dung cần kiểm duyệt.',
            'is_visible' => true,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.reviews.visibility', $review), [
                'is_visible' => 0,
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertFalse($review->refresh()->is_visible);

        $this->get(route('client.products.show', $product->slug))
            ->assertOk()
            ->assertDontSee('Nội dung cần kiểm duyệt.')
            ->assertSee('Chưa có đánh giá');

        $this->patch(route('admin.reviews.visibility', $review), [
            'is_visible' => 1,
        ])->assertSessionHas('success');

        $this->assertTrue($review->refresh()->is_visible);

        $this->get(route('client.products.show', $product->slug))
            ->assertOk()
            ->assertSee('Nội dung cần kiểm duyệt.')
            ->assertSee('4.0/5');
    }

    public function test_hidden_reviews_are_excluded_from_public_rating_average(): void
    {
        [, $customer, $product] = $this->fixture();

        Review::create([
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'rating' => 2,
            'content' => 'Đánh giá đang hiển thị.',
            'is_visible' => true,
        ]);

        Review::create([
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'rating' => 5,
            'content' => 'Đánh giá đã bị ẩn.',
            'is_visible' => false,
        ]);

        $this->get(route('client.products.show', $product->slug))
            ->assertOk()
            ->assertSee('2.0/5')
            ->assertSee('1 đánh giá')
            ->assertSee('Đánh giá đang hiển thị.')
            ->assertDontSee('Đánh giá đã bị ẩn.');
    }

    public function test_customer_cannot_access_review_management(): void
    {
        [, $customer] = $this->fixture();

        $this->actingAs($customer)
            ->get(route('admin.reviews.index'))
            ->assertForbidden();
    }

    public function test_admin_review_uses_the_same_media_validation_rules(): void
    {
        Storage::fake('public');
        [$admin, , $product] = $this->fixture();

        $this->actingAs($admin)
            ->post(route('admin.reviews.store'), [
                'product_id' => $product->id,
                'rating' => 5,
                'content' => 'Đánh giá có tệp không hợp lệ.',
                'is_visible' => 1,
                'public_name' => 'Nguyễn Minh Anh',
                'media' => [
                    UploadedFile::fake()->create('tai-lieu.pdf', 100, 'application/pdf'),
                ],
            ])
            ->assertSessionHasErrors('media.0');

        $this->assertDatabaseCount('reviews', 0);
        $this->assertDatabaseCount('review_media', 0);
    }

    public function test_admin_review_accepts_a_custom_unmasked_public_name(): void
    {
        [$admin, , $product] = $this->fixture();

        $this->actingAs($admin)
            ->post(route('admin.reviews.store'), [
                'product_id' => $product->id,
                'rating' => 5,
                'content' => 'Nội dung dùng tên do admin tự nhập.',
                'is_visible' => 1,
                'public_name' => 'Nguyễn Văn Thành',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'public_name' => 'Nguyễn Văn Thành',
            'is_admin_review' => true,
        ]);
    }

    public function test_admin_review_does_not_accept_the_admin_account_identity_as_public_name(): void
    {
        [$admin, , $product] = $this->fixture();

        $this->actingAs($admin)
            ->post(route('admin.reviews.store'), [
                'product_id' => $product->id,
                'rating' => 5,
                'content' => 'Nội dung kiểm tra bảo mật danh tính.',
                'is_visible' => 1,
                'public_name' => $admin->name,
            ])
            ->assertSessionHasErrors('public_name');

        $this->assertDatabaseCount('reviews', 0);
    }

    /**
     * @return array{User, User, Product}
     */
    private function fixture(): array
    {
        $suffix = Str::lower(Str::random(8));
        $admin = User::factory()->create([
            'name' => 'Quản trị viên nội bộ',
            'email' => "quan-tri-{$suffix}@maxball.test",
            'role' => 'admin',
            'status' => true,
        ]);
        $customer = User::factory()->create([
            'role' => 'customer',
            'status' => true,
        ]);
        $category = Category::create([
            'name' => 'Danh mục quản lý đánh giá',
            'slug' => "danh-muc-review-admin-{$suffix}",
            'status' => true,
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Sản phẩm quản lý đánh giá',
            'slug' => "san-pham-review-admin-{$suffix}",
            'status' => true,
            'base_price' => 350000,
        ]);

        return [$admin, $customer, $product];
    }
}
