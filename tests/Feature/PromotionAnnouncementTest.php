<?php

namespace Tests\Feature;

use App\Models\PromotionAnnouncement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionAnnouncementTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_displays_multiple_active_promotions_in_a_centered_modal(): void
    {
        PromotionAnnouncement::findOrFail(1)->update([
            'title' => 'Ưu đãi tháng tám',
            'content' => 'Đánh giá đơn hàng để nhận voucher freeship.',
            'is_active' => true,
        ]);

        PromotionAnnouncement::create([
            'title' => 'Ưu đãi cuối tuần',
            'content' => 'Mua hai sản phẩm được miễn phí vận chuyển.',
            'is_active' => true,
        ]);

        PromotionAnnouncement::create([
            'title' => 'Thông báo đang ẩn',
            'content' => 'Khách không được thấy nội dung này.',
            'is_active' => false,
        ]);

        $this->get(route('client.home'))
            ->assertOk()
            ->assertSee('family=Be+Vietnam+Pro', false)
            ->assertSee('https://zalo.me/0383846482', false)
            ->assertSee('id="maxball-promotion-toggle"', false)
            ->assertSee('id="maxball-promotion-modal"', false)
            ->assertSee('place-items: center', false)
            ->assertSee('Ưu đãi tháng tám')
            ->assertSee('Ưu đãi cuối tuần')
            ->assertSee('data-promotion-next', false)
            ->assertDontSee('Thông báo đang ẩn');
    }

    public function test_admin_can_create_update_toggle_and_delete_promotions(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.promotion-announcements.index'))
            ->assertOk()
            ->assertSee('Thông báo khuyến mãi')
            ->assertSee(route('admin.coupons.index'), false)
            ->assertSee('ml-7 border-l', false);

        $this->post(route('admin.promotion-announcements.store'), [
            'title' => 'Ưu đãi mới',
            'content' => 'Nội dung ưu đãi mới.',
            'is_active' => '1',
        ])->assertRedirect(route('admin.promotion-announcements.index'));

        $announcement = PromotionAnnouncement::query()
            ->where('title', 'Ưu đãi mới')
            ->firstOrFail();

        $this->assertTrue($announcement->is_active);

        $this->put(route('admin.promotion-announcements.update', $announcement), [
            'title' => 'Ưu đãi đã sửa',
            'content' => 'Nội dung đã được cập nhật.',
        ])->assertRedirect(route('admin.promotion-announcements.index'));

        $this->assertDatabaseHas('promotion_announcements', [
            'id' => $announcement->id,
            'title' => 'Ưu đãi đã sửa',
            'is_active' => false,
        ]);

        $this->patch(route('admin.promotion-announcements.toggle-status', $announcement))
            ->assertRedirect();

        $this->assertDatabaseHas('promotion_announcements', [
            'id' => $announcement->id,
            'is_active' => true,
        ]);

        $this->delete(route('admin.promotion-announcements.destroy', $announcement))
            ->assertRedirect(route('admin.promotion-announcements.index'));

        $this->assertDatabaseMissing('promotion_announcements', [
            'id' => $announcement->id,
        ]);
    }

    public function test_gift_button_is_hidden_when_there_are_no_active_promotions(): void
    {
        PromotionAnnouncement::query()->update(['is_active' => false]);

        $this->get(route('client.home'))
            ->assertOk()
            ->assertDontSee('id="maxball-promotion-toggle"', false)
            ->assertDontSee('id="maxball-promotion-modal"', false)
            ->assertSee('id="maxball-zalo-link"', false);
    }

    public function test_customer_cannot_manage_promotion_announcements(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'status' => true,
        ]);

        $this->actingAs($customer)
            ->get(route('admin.promotion-announcements.index'))
            ->assertForbidden();
    }
}
