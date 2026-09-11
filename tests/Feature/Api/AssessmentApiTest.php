<?php

namespace Tests\Feature\Api;

use App\Enums\SubscriptionStatus;
use App\Models\AssessmentLog;
use App\Models\LlmSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AssessmentApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->enableLlm();
    }

    private function enableLlm(string $apiKey = 'test-key'): void
    {
        config([
            'llm.api_key' => $apiKey,
            'llm.base_url' => 'https://api.openai.com/v1',
            'llm.model' => 'gpt-4o-mini',
        ]);

        LlmSetting::current()->update([
            'is_enabled' => true,
            'provider' => 'openai',
            'base_url' => 'https://api.openai.com/v1',
            'api_key' => $apiKey,
            'model' => 'gpt-4o-mini',
            'timeout' => 30,
        ]);
    }

    private function premiumUser(): User
    {
        return User::factory()->create([
            'current_cefr_level' => 'C1',
            'remaining_trial_sessions' => 5,
            'subscription_status' => SubscriptionStatus::PREMIUM_MONTHLY,
        ]);
    }

    public function test_ielts_evaluate_returns_band_and_persists_log(): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [[
                    'message' => ['content' => json_encode([
                        'scores' => [
                            'fluency_coherence' => 6.0,
                            'lexical_resource' => 6.5,
                            'grammatical_range_accuracy' => 5.5,
                            'pronunciation_estimate' => 6.0,
                            'overall_band' => 6.0,
                        ],
                        'fluency_matrix' => ['s_total' => 0.65, 'final_fluency' => 1],
                        'content_alignment' => ['is_on_topic' => true, 'relevance_score' => 0.9],
                        'corrections' => [[
                            'original' => 'I go with my family',
                            'corrected' => 'I went with my family',
                            'issue_type' => 'Grammar (Tense)',
                            'explanation' => 'Use past simple.',
                        ]],
                        'feedback_summary' => 'Good flow.',
                    ])],
                ]],
            ], 200),
        ]);

        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $this->postJson('api/v1/assessment/evaluate', [
            'test_type' => 'IELTS',
            'task_type' => 'SPEAKING_PART_2',
            'prompt_question' => 'Describe a memorable trip you took in the past year.',
            'user_transcript' => 'I want to talk about my visit to Malang last year. I went with my family...',
            'duration_seconds' => 95,
        ])->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.test_type', 'IELTS')
            ->assertJsonPath('data.scores.overall_band', 6)
            ->assertJsonPath('data.scores.overall_score', 6)
            ->assertJsonPath('data.fluency_matrix.s_total', 0.65)
            ->assertJsonPath('data.fluency_matrix.final_fluency', 1)
            ->assertJsonPath('data.content_alignment.is_on_topic', true)
            ->assertJsonPath('data.corrections.0.original', 'I go with my family');

        $this->assertDatabaseHas('assessment_logs', [
            'user_id' => $user->id,
            'test_type' => 'IELTS',
            'task_type' => 'SPEAKING_PART_2',
            'overall_score' => 6.0,
            'final_fluency' => 1,
            'is_on_topic' => 1,
        ]);
    }

    public function test_toefl_evaluate_returns_scaled_score(): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [[
                    'message' => ['content' => json_encode([
                        'scores' => [
                            'delivery' => 3,
                            'language_use' => 2,
                            'topic_development' => 3,
                            'raw_score' => 2.67,
                            'scaled_score_30' => 20,
                        ],
                        'fluency_matrix' => ['s_total' => 0.68, 'final_fluency' => 1],
                        'content_alignment' => ['is_on_topic' => true, 'relevance_score' => 0.85],
                        'corrections' => [],
                        'feedback_summary' => 'Clear response.',
                    ])],
                ]],
            ], 200),
        ]);

        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $this->postJson('api/v1/assessment/evaluate', [
            'test_type' => 'TOEFL',
            'task_type' => 'INDEPENDENT_TASK',
            'prompt_question' => 'Do you agree or disagree?',
            'user_transcript' => 'I strongly agree because...',
        ])->assertOk()
            ->assertJsonPath('data.scores.scaled_score_30', 20)
            ->assertJsonPath('data.scores.overall_score', 20)
            ->assertJsonPath('data.fluency_matrix.final_fluency', 1);

        $this->assertDatabaseHas('assessment_logs', [
            'user_id' => $user->id,
            'test_type' => 'TOEFL',
            'overall_score' => 20,
        ]);
    }

    public function test_backend_safety_guard_overrides_final_fluency_below_threshold(): void
    {
        // LLM mengklaim final_fluency=1 meski s_total 0.2 -> backend harus menimpa jadi 0.
        Http::fake([
            '*' => Http::response([
                'choices' => [[
                    'message' => ['content' => json_encode([
                        'scores' => [
                            'fluency_coherence' => 2.0,
                            'lexical_resource' => 3.0,
                            'grammatical_range_accuracy' => 3.0,
                            'pronunciation_estimate' => 3.0,
                            'overall_band' => 2.5,
                        ],
                        'fluency_matrix' => ['s_total' => 0.2, 'final_fluency' => 1],
                        'content_alignment' => ['is_on_topic' => true, 'relevance_score' => 0.5],
                        'corrections' => [],
                        'feedback_summary' => 'Low fluency.',
                    ])],
                ]],
            ], 200),
        ]);

        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $this->postJson('api/v1/assessment/evaluate', [
            'test_type' => 'IELTS',
            'task_type' => 'SPEAKING_PART_1',
            'prompt_question' => 'Where do you live?',
            'user_transcript' => 'I live in Jakarta.',
        ])->assertOk()
            ->assertJsonPath('data.fluency_matrix.s_total', 0.2)
            ->assertJsonPath('data.fluency_matrix.final_fluency', 0);

        $this->assertDatabaseHas('assessment_logs', [
            'user_id' => $user->id,
            'final_fluency' => 0,
            'overall_score' => 2.5,
        ]);
    }

    public function test_evaluate_rejects_invalid_test_type(): void
    {
        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $this->postJson('api/v1/assessment/evaluate', [
            'test_type' => 'PTE',
            'task_type' => 'SPEAKING_PART_1',
            'prompt_question' => 'What is your name?',
            'user_transcript' => 'My name is Budi.',
        ])->assertStatus(422)
            ->assertJsonValidationErrors('test_type');
    }

    public function test_evaluate_returns_422_when_llm_disabled(): void
    {
        config(['llm.api_key' => '']);
        LlmSetting::current()->update(['is_enabled' => false, 'api_key' => null]);

        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $this->postJson('api/v1/assessment/evaluate', [
            'test_type' => 'IELTS',
            'task_type' => 'SPEAKING_PART_2',
            'prompt_question' => 'Describe a place you like.',
            'user_transcript' => 'I like the beach.',
        ])->assertStatus(422);
    }

    public function test_requires_authentication(): void
    {
        $this->postJson('api/v1/assessment/evaluate', [
            'test_type' => 'IELTS',
            'task_type' => 'SPEAKING_PART_2',
            'prompt_question' => 'Describe a place you like.',
            'user_transcript' => 'I like the beach.',
        ])->assertStatus(401);
    }

    public function test_assessment_log_stores_full_raw_response(): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [[
                    'message' => ['content' => json_encode([
                        'scores' => [
                            'fluency_coherence' => 7.0,
                            'lexical_resource' => 7.0,
                            'grammatical_range_accuracy' => 7.0,
                            'pronunciation_estimate' => 7.0,
                            'overall_band' => 7.0,
                        ],
                        'fluency_matrix' => ['s_total' => 0.8, 'final_fluency' => 1],
                        'content_alignment' => ['is_on_topic' => true, 'relevance_score' => 1.0],
                        'corrections' => [],
                        'feedback_summary' => 'Excellent.',
                    ])],
                ]],
            ], 200),
        ]);

        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $this->postJson('api/v1/assessment/evaluate', [
            'test_type' => 'IELTS',
            'task_type' => 'SPEAKING_PART_3',
            'prompt_question' => 'Why do people travel?',
            'user_transcript' => 'People travel to learn and relax.',
        ])->assertOk();

        $log = AssessmentLog::where('user_id', $user->id)->first();
        $this->assertNotNull($log);
        $this->assertSame(7.0, (float) $log->overall_score);
        $this->assertSame(0.8, (float) $log->s_total);
        $this->assertIsArray($log->raw_response_json);
        $this->assertSame(7, $log->raw_response_json['scores']['overall_band']);
        $this->assertSame('IELTS', $log->raw_response_json['test_type']);
    }
}
