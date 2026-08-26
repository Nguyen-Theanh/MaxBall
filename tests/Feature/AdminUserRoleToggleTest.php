<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserRoleToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_promote_a_customer_to_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => true]);
        $customer = User::factory()->create(['role' => 'customer', 'status' => true]);

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->patch(route('admin.users.toggle-role', $customer))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_demote_another_admin_to_customer(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => true]);
        $otherAdmin = User::factory()->create(['role' => 'admin', 'status' => true]);

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->patch(route('admin.users.toggle-role', $otherAdmin))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $otherAdmin->id,
            'role' => 'customer',
        ]);
    }

    public function test_admin_cannot_change_their_own_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => true]);

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->patch(route('admin.users.toggle-role', $admin))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role' => 'admin',
        ]);
    }

    public function test_customer_cannot_toggle_user_roles(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'status' => true]);
        $anotherCustomer = User::factory()->create(['role' => 'customer', 'status' => true]);

        $this->actingAs($customer)
            ->patch(route('admin.users.toggle-role', $anotherCustomer))
            ->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $anotherCustomer->id,
            'role' => 'customer',
        ]);
    }

    public function test_user_management_page_shows_role_switches_and_disables_the_current_admin_switch(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => true]);
        $customer = User::factory()->create(['role' => 'customer', 'status' => true]);

        $response = $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('role-toggle-'.$admin->id, false)
            ->assertSee('role-toggle-'.$customer->id, false)
            ->assertSee(route('admin.users.toggle-role', $customer), false)
            ->assertSee('HTMLFormElement.prototype.submit.call(form)', false);

        $html = $response->getContent();
        $this->assertMatchesRegularExpression('/id="role-toggle-'.$admin->id.'"[^>]*disabled/s', $html);
        $this->assertDoesNotMatchRegularExpression('/id="role-toggle-'.$customer->id.'"[^>]*disabled/s', $html);
    }
}
