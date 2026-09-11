<?php

namespace Tests\Feature\Api;

use App\Enums\SessionMode;
use App\Enums\SessionStepState;
use App\Models\ConversationLog;
use App\Models\ConversationSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserStatsApiTest extends TestCase
{
    use RefreshDatabase;

    private function createCompletedSession(int $userId, string $completedAt, int $logs = 1): void
    {
        $session = ConversationSession::create([
            'user_id' => $userId,
            'mode' => SessionMode::ADAPTIVE,
            'start_level' => 'A1',
            'current_level' => 'A2',
            'total_turns_planned' => 5,
            'status' => 'COMPLETED',
            'completed_at' => $completedAt,
        ]);

        foreach (range(1, $logs) as $turn) {
            ConversationLog::create([
                'user_id' => $userId,
                'session_id' => $session->id,
                'turn_number' => $turn,
                'user_response_text' => $turn === 1 ? 'Yesterday I go to the market.' : 'Good response.',
                'total_turn_score' => 2,
                'score_word_count' => 1,
                'score_grammar' => 1,
                'has_error' => $turn === 1,
                'correct_way_text' => $turn === 1 ? 'Yesterday I went to the market.' : null,
                'step_state' => SessionStepState::NORMAL,
            ]);
        }
    }

    public function test_stats_are_zero_for_new_user(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('api/v1/user/stats')
            ->assertOk()
            ->assertJsonPath('data.total_sessions_completed', 0)
            ->assertJsonPath('data.total_turns', 0)
            ->assertJsonPath('data.current_streak_days', 0)
            ->assertJsonPath('data.longest_streak_days', 0)
            ->assertJsonPath('data.phrases_learned', 0)
            ->assertJsonPath('data.current_cefr_level', 'A1');
    }

    public function test_stats_aggregate_sessions_turns_and_streak(): void
    {
        $user = User::factory()->create(['current_cefr_level' => 'A2']);
        Sanctum::actingAs($user);

        // Latihan 3 hari berturut-turut (hari ini, kemarin, 2 hari lalu).
        $this->createCompletedSession($user->id, now()->toDateString().' 08:00:00');
        $this->createCompletedSession($user->id, now()->subDay()->toDateString().' 09:00:00');
        $this->createCompletedSession($user->id, now()->subDays(2)->toDateString().' 10:00:00');

        $response = $this->getJson('api/v1/user/stats')
            ->assertOk()
            ->assertJsonPath('data.current_cefr_level', 'A2')
            ->assertJsonPath('data.total_sessions_completed', 3)
            ->assertJsonPath('data.total_turns', 3)
            ->assertJsonPath('data.avg_turn_score', 2)
            ->assertJsonPath('data.grammar_accuracy_pct', 100)
            ->assertJsonPath('data.phrases_learned', 1)
            ->assertJsonPath('data.current_streak_days', 3)
            ->assertJsonPath('data.longest_streak_days', 3);

        $this->assertArrayHasKey('remaining_trial_sessions', $response->json('data'));
        $this->assertArrayHasKey('is_premium', $response->json('data'));
    }

    public function test_streak_breaks_when_last_activity_is_old(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->createCompletedSession($user->id, now()->subDays(3)->toDateString().' 08:00:00');
        $this->createCompletedSession($user->id, now()->subDays(4)->toDateString().' 08:00:00');

        $this->getJson('api/v1/user/stats')
            ->assertOk()
            ->assertJsonPath('data.current_streak_days', 0)
            ->assertJsonPath('data.longest_streak_days', 2);
    }

    public function test_stats_requires_authentication(): void
    {
        $this->getJson('api/v1/user/stats')->assertUnauthorized();
    }
}