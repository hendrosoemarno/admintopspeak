<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_store_grammar_rule(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $this->postJson('api/v1/admin/grammar-rules', [
            'rule_code' => 'SVA_TEST_01',
            'category' => 'Subject-Verb Agreement',
            'cefr_level' => 'A2',
            'regex_pattern' => '/\b(he|she|it)\s+go\b/i',
            'description' => 'He/she/it must be followed by the -s verb form.',
        ])->assertCreated()
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('grammar_rules', ['rule_code' => 'SVA_TEST_01']);
    }

    public function test_non_admin_is_forbidden_from_admin_endpoint(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('api/v1/admin/grammar-rules', [
            'rule_code' => 'SVA_TEST_02',
            'category' => 'Subject-Verb Agreement',
            'cefr_level' => 'A2',
            'regex_pattern' => '/\b(he|she|it)\s+go\b/i',
        ])->assertForbidden();

        $this->assertDatabaseMissing('grammar_rules', ['rule_code' => 'SVA_TEST_02']);
    }

    public function test_admin_can_update_app_configuration(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $this->postJson('api/v1/admin/app-config', [
            'latest_app_version' => '2.0.0',
            'min_required_version' => '1.5.0',
            'is_force_update' => true,
            'play_store_url' => 'https://play.google.com/store/apps/details?id=com.topspeak.app',
            'update_message' => 'Please update to continue using TopSpeak.',
        ])->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('app_configurations', ['latest_app_version' => '2.0.0']);
    }
}