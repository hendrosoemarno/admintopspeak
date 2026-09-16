<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SystemApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_app_version_is_public(): void
    {
        $this->getJson('api/v1/app-version')
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure(['data' => ['latest_version', 'min_required_version', 'is_force_update']]);
    }

    public function test_profile_requires_authentication(): void
    {
        $this->getJson('api/v1/user/profile')->assertUnauthorized();
    }

    public function test_profile_includes_free_session_quota_denominator(): void
    {
        $user = User::factory()->create([
            'remaining_trial_sessions' => 0,
            'total_free_sessions_granted' => 5,
        ]);
        Sanctum::actingAs($user);

        $this->getJson('api/v1/user/profile')
            ->assertOk()
            ->assertJsonPath('data.remaining_trial_sessions', 0)
            ->assertJsonPath('data.total_free_sessions_granted', 5);
    }
}