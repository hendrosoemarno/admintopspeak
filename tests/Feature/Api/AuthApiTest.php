<?php

namespace Tests\Feature\Api;

use App\Models\AppConfiguration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_device_returns_token_and_user(): void
    {
        $response = $this->postJson('api/v1/auth/register-device', [
            'device_uuid' => '550e8400-e29b-41d4-a716-446655440000',
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'data' => ['access_token', 'token_type', 'user' => ['id', 'name', 'email', 'is_guest', 'current_cefr_level']],
            ])
            ->assertJsonPath('data.user.is_guest', true)
            ->assertJsonPath('data.user.name', 'Guest Learner');

        $this->assertStringEndsWith('@topspeak.app', $response->json('data.user.email'));

        $this->assertDatabaseHas('users', [
            'device_uuid' => '550e8400-e29b-41d4-a716-446655440000',
        ]);

        $user = User::where('device_uuid', '550e8400-e29b-41d4-a716-446655440000')->first();
        $this->assertSame('A1', $user->current_cefr_level->value);
        $this->assertSame(1, $user->remaining_trial_sessions);
    }

    public function test_register_device_uses_free_tier_config_for_initial_sessions(): void
    {
        AppConfiguration::current()->update(['free_tier_initial_sessions' => 3]);

        $this->postJson('api/v1/auth/register-device', [
            'device_uuid' => '550e8400-e29b-41d4-a716-446655449999',
        ])->assertCreated();

        $user = User::where('device_uuid', '550e8400-e29b-41d4-a716-446655449999')->first();
        $this->assertSame(3, $user->remaining_trial_sessions);
    }

    public function test_register_device_requires_valid_uuid(): void
    {
        $this->postJson('api/v1/auth/register-device', ['device_uuid' => 'not-a-uuid'])
            ->assertUnprocessable()
            ->assertJsonPath('status', 'error');
    }

    public function test_register_upgrades_guest_to_email_account(): void
    {
        $guest = User::factory()->create(['device_uuid' => '550e8400-e29b-41d4-a716-446655440001']);
        Sanctum::actingAs($guest);

        $this->postJson('api/v1/auth/register', [
            'name' => 'Budi Santoso',
            'email' => 'BUDI@Example.Com',
            'password' => 'secret123',
        ])
            ->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.user.name', 'Budi Santoso')
            ->assertJsonPath('data.user.email', 'budi@example.com')
            ->assertJsonPath('data.user.is_guest', false);

        $this->assertDatabaseHas('users', [
            'id' => $guest->id,
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
        ]);

        $user = User::find($guest->id);
        $this->assertTrue(Hash::check('secret123', $user->password));
        $this->assertSame('550e8400-e29b-41d4-a716-446655440001', $user->device_uuid);
    }

    public function test_register_with_existing_email_and_matching_password_restores_account_on_new_device(): void
    {
        $account = User::factory()->create([
            'email' => 'budi@example.com',
            'name' => 'Budi Lama',
            'device_uuid' => '550e8400-e29b-41d4-a716-446655440088',
        ]);

        $guest = User::factory()->create(['device_uuid' => '550e8400-e29b-41d4-a716-446655440099']);
        Sanctum::actingAs($guest);

        $oldToken = $account->createToken('old-device-token');

        $this->postJson('api/v1/auth/register', [
            'name' => 'Budi Baru',
            'email' => 'budi@example.com',
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.user.id', $account->id);

        $this->assertTrue(User::where('id', $guest->id)->doesntExist());
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440099', $account->fresh()->device_uuid);

        // Kebijakan satu perangkat aktif: token lama perangkat lama dicabut,
        // hanya token baru untuk perangkat sekarang yang tersisa.
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $oldToken->accessToken->id]);
        $this->assertSame(1, $account->fresh()->tokens()->count());
    }

    public function test_register_with_existing_email_and_wrong_password_rejected(): void
    {
        $account = User::factory()->create([
            'email' => 'budi@example.com',
            'device_uuid' => '550e8400-e29b-41d4-a716-446655440088',
        ]);

        $guest = User::factory()->create(['device_uuid' => '550e8400-e29b-41d4-a716-446655440099']);
        Sanctum::actingAs($guest);

        $this->postJson('api/v1/auth/register', [
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'password' => 'wrong-password',
        ])
            ->assertStatus(422)
            ->assertJsonPath('status', 'error');

        $this->assertDatabaseHas('users', ['id' => $guest->id]);
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440088', $account->fresh()->device_uuid);
    }

    public function test_register_requires_authentication(): void
    {
        $this->postJson('api/v1/auth/register', [
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'password' => 'secret123',
        ])->assertUnauthorized();
    }

    public function test_logout_revokes_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('device-token')->plainTextToken;

        $this->withToken($token)->postJson('api/v1/auth/logout')
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_logout_without_token_returns_401_quickly(): void
    {
        $this->postJson('api/v1/auth/logout')
            ->assertUnauthorized()
            ->assertJsonPath('status', 'error');
    }

    public function test_login_success_on_current_device_returns_token(): void
    {
        $user = User::factory()->create([
            'email' => 'loginflow@test.com',
            'password' => Hash::make('password123'),
        ]);
        Sanctum::actingAs($user);

        $this->postJson('api/v1/auth/login', [
            'email' => 'loginflow@test.com',
            'password' => 'password123',
        ])
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Login berhasil.')
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.email', 'loginflow@test.com')
            ->assertJsonPath('data.user.is_guest', false)
            ->assertJsonStructure([
                'data' => ['token', 'access_token', 'token_type', 'user'],
            ]);
    }

    public function test_login_restores_account_on_new_device_and_revokes_old_tokens(): void
    {
        $account = User::factory()->create([
            'email' => 'loginflow@test.com',
            'password' => Hash::make('password123'),
            'device_uuid' => '550e8400-e29b-41d4-a716-446655440088',
        ]);
        $oldToken = $account->createToken('old-device-token');

        $guest = User::factory()->create(['device_uuid' => '550e8400-e29b-41d4-a716-446655440099']);
        Sanctum::actingAs($guest);

        $this->postJson('api/v1/auth/login', [
            'email' => 'loginflow@test.com',
            'password' => 'password123',
        ])
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.user.id', $account->id);

        $this->assertTrue(User::where('id', $guest->id)->doesntExist());
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440099', $account->fresh()->device_uuid);
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $oldToken->accessToken->id]);
        $this->assertSame(1, $account->fresh()->tokens()->count());
    }

    public function test_login_with_unregistered_email_returns_404_and_does_not_create_account(): void
    {
        $guest = User::factory()->create([
            'name' => 'Guest Learner',
            'email' => 'guest-logintest@topspeak.app',
            'device_uuid' => '550e8400-e29b-41d4-a716-446655440099',
        ]);
        Sanctum::actingAs($guest);

        $countBefore = User::count();

        $this->postJson('api/v1/auth/login', [
            'email' => 'orangbaru@test.com',
            'password' => 'password123',
        ])
            ->assertNotFound()
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Email tidak terdaftar.');

        $this->assertSame($countBefore, User::count());
    }

    public function test_login_with_wrong_password_returns_401(): void
    {
        User::factory()->create([
            'email' => 'loginflow@test.com',
            'password' => Hash::make('password123'),
            'device_uuid' => '550e8400-e29b-41d4-a716-446655440088',
        ]);
        $guest = User::factory()->create(['device_uuid' => '550e8400-e29b-41d4-a716-446655440099']);
        Sanctum::actingAs($guest);

        $this->postJson('api/v1/auth/login', [
            'email' => 'loginflow@test.com',
            'password' => 'salah',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Email atau password salah.');
    }

    public function test_login_rejects_guest_account_email(): void
    {
        $guest = User::factory()->create([
            'name' => 'Guest Learner',
            'email' => 'guest-logintest@topspeak.app',
            'device_uuid' => '550e8400-e29b-41d4-a716-446655440099',
        ]);
        Sanctum::actingAs($guest);

        $this->postJson('api/v1/auth/login', [
            'email' => $guest->email,
            'password' => 'password',
        ])
            ->assertNotFound()
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Email tidak terdaftar.');
    }

    public function test_login_requires_email_and_password(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('api/v1/auth/login', ['email' => 'bukan-email'])
            ->assertUnprocessable()
            ->assertJsonPath('status', 'error');
    }

    public function test_login_requires_authentication(): void
    {
        $this->postJson('api/v1/auth/login', [
            'email' => 'loginflow@test.com',
            'password' => 'password123',
        ])->assertUnauthorized()
            ->assertJsonPath('status', 'error');
    }

    public function test_login_rate_limited_after_five_attempts(): void
    {
        User::factory()->create([
            'email' => 'loginflow@test.com',
            'password' => Hash::make('password123'),
            'device_uuid' => '550e8400-e29b-41d4-a716-446655440088',
        ]);
        $guest = User::factory()->create(['device_uuid' => '550e8400-e29b-41d4-a716-446655440099']);
        Sanctum::actingAs($guest);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('api/v1/auth/login', [
                'email' => 'loginflow@test.com',
                'password' => 'salah',
            ])->assertUnauthorized();
        }

        $this->postJson('api/v1/auth/login', [
            'email' => 'loginflow@test.com',
            'password' => 'password123',
        ])
            ->assertStatus(429)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Terlalu banyak percobaan, coba lagi nanti.');
    }

    public function test_deprecated_wa_otp_and_verify_routes_are_removed(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('api/v1/auth/request-wa-otp', ['phone_number' => '628123456789'])->assertNotFound();
        $this->postJson('api/v1/auth/verify-wa', [
            'phone_number' => '628123456789',
            'otp_code' => '123456',
        ])->assertNotFound();
    }
}