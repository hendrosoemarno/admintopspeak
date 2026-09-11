<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
}