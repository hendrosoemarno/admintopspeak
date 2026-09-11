<?php

namespace Tests\Feature;

use App\Livewire\Admin\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_login_page_renders(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('TopSpeak Admin');
    }

    public function test_admin_can_login_and_is_redirected_to_dashboard(): void
    {
        User::factory()->admin()->create([
            'email' => 'admin@topspeak.app',
            'password' => 'Admin123!',
        ]);

        Livewire::test(Login::class)
            ->set('email', 'admin@topspeak.app')
            ->set('password', 'Admin123!')
            ->call('login')
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_login_rejects_wrong_password(): void
    {
        User::factory()->admin()->create([
            'email' => 'admin@topspeak.app',
            'password' => 'Admin123!',
        ]);

        Livewire::test(Login::class)
            ->set('email', 'admin@topspeak.app')
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors(['password']);

        $this->assertGuest();
    }

    public function test_non_admin_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'user@topspeak.test',
            'password' => 'password',
            'is_admin' => false,
        ]);

        Livewire::test(Login::class)
            ->set('email', 'user@topspeak.test')
            ->set('password', 'password')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_authenticated_non_admin_gets_forbidden_on_admin_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_logout_returns_to_login(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }
}