<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_riders_can_sign_in_from_the_frontend(): void
    {
        $rider = User::factory()->rider()->create([
            'email' => 'rider@example.test',
            'password' => 'secret-password',
        ]);

        $this->post(route('rider.login.store'), [
            'email' => $rider->email,
            'password' => 'secret-password',
        ])->assertRedirect(route('rider.panel'));

        $this->assertAuthenticatedAs($rider);
    }

    public function test_role_specific_login_links_redirect_to_the_unified_login(): void
    {
        $this->get(route('rider.login'))->assertRedirect(route('login'));
        $this->get(route('customer.login'))->assertRedirect(route('login'));
    }

    public function test_normal_login_page_uses_saved_account_role_instead_of_a_role_selector(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('te llevaremos al área correspondiente a tu cuenta')
            ->assertSee(route('login.store'))
            ->assertDontSee('Elige tu rol');

        $this->get(route('access'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_see_a_prominent_logout_button(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Cerrar sesión')
            ->assertSee(route('logout'));
    }

    public function test_logout_returns_to_the_role_aware_login_page(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->post(route('logout'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->assertGuest();
    }

    public function test_normal_login_redirects_users_to_the_area_for_their_saved_role(): void
    {
        $destinations = [
            'customer' => route('home'),
            'rider' => route('rider.panel'),
            'owner' => '/admin',
            'admin' => '/admin',
        ];

        foreach ($destinations as $role => $destination) {
            $user = User::factory()->{$role}()->create();

            $this->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'password',
            ])->assertRedirect($destination);

            $this->assertAuthenticatedAs($user);

            $this->post(route('logout'))->assertRedirect(route('login'));
            $this->assertGuest();
        }
    }

    public function test_a_customer_cannot_sign_in_through_the_rider_form(): void
    {
        $customer = User::factory()->customer()->create([
            'email' => 'customer@example.test',
            'password' => 'secret-password',
        ]);

        $this->from(route('rider.login'))->post(route('rider.login.store'), [
            'email' => $customer->email,
            'password' => 'secret-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_only_admins_and_owners_can_enter_the_filament_panel(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)->get('/admin')->assertForbidden();
    }

    public function test_admin_can_view_all_user_roles_but_owners_cannot(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create([
            'name' => 'Cliente visible',
            'email' => 'customer-visible@example.test',
        ]);

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk()
            ->assertSee('Usuarios y roles')
            ->assertSee($customer->email)
            ->assertSee('Customer');

        $owner = User::factory()->owner()->create();
        $this->actingAs($owner)
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_riders_are_sent_to_their_login_and_owners_can_open_the_panel(): void
    {
        $this->get('/rider')->assertRedirect(route('rider.login'));

        $owner = User::factory()->owner()->create();
        $this->actingAs($owner)->get('/admin')->assertOk();
    }
}
