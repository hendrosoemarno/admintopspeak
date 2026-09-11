<?php

namespace Tests\Feature\Api;

use App\Enums\SubscriptionStatus;
use App\Models\Lesson;
use App\Models\LlmSetting;
use App\Models\Question;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProgressPredictorApiTest extends TestCase
{
    use RefreshDatabase;

    private const GOOD_ANSWER = 'My hometown is a small coastal city in Java, and I live in a close-knit family community.';

    /** @var array<int, Lesson> */
    private array $lessons = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedCurriculum();
        $this->fakeLlm();
    }

    private function seedCurriculum(): void
    {
        $unit = Unit::create([
            'unit_number' => 1,
            'title' => 'Home & Family',
            'part' => 1,
            'outcome' => 'Menjawab pertanyaan pribadi tentang rumah dan keluarga.',
        ]);

        foreach (range(1, 4) as $n) {
            $lesson = Lesson::create([
                'unit_id' => $unit->id,
                'lesson_number' => $n,
                'title' => "Lesson {$n}",
                'difficulty' => 'Easy',
            ]);

            foreach (range(1, 5) as $i) {
                Question::create([
                    'lesson_id' => $lesson->id,
                    'question_text' => "Question {$i} for lesson {$n}?",
                    'model_answer' => 'Model answer describing a close-knit family living in a residential area.',
                    'key_point' => 'close-knit family',
                ]);
            }

            $this->lessons[] = $lesson;
        }
    }

    private function fakeLlm(): void
    {
        config([
            'llm.api_key' => 'test-key',
            'llm.base_url' => 'https://api.openai.com/v1',
            'llm.model' => 'gpt-4o-mini',
        ]);

        LlmSetting::current()->update([
            'is_enabled' => true,
            'provider' => 'openai',
            'base_url' => 'https://api.openai.com/v1',
            'api_key' => 'test-key',
            'model' => 'gpt-4o-mini',
            'timeout' => 30,
        ]);

        Http::fake([
            '*' => function ($request) {
                $transcript = '';
                foreach (data_get($request->data(), 'messages', []) as $m) {
                    if (($m['role'] ?? '') === 'user') {
                        $transcript = $m['content'] ?? '';
                    }
                }

                $good = str_contains($transcript, self::GOOD_ANSWER);

                return Http::response([
                    'choices' => [[
                        'message' => ['content' => json_encode([
                            'key_point_detected' => $good,
                            'key_point_score' => $good ? 0.9 : 0.2,
                            'grammar_score' => $good ? 0.85 : 0.6,
                            'lexical_score' => $good ? 0.8 : 0.5,
                            'grammar_feedback' => 'ok',
                            'vocabulary_feedback' => 'ok',
                            'suggested_answer' => $good ? '' : 'Yes, my close-knit family lives in a small coastal city.',
                        ])],
                    ]],
                ], 200);
            },
        ]);
    }

    private function premiumUser(): User
    {
        return User::factory()->create([
            'subscription_status' => SubscriptionStatus::PREMIUM_MONTHLY,
        ]);
    }

    /** Submit 4 jawaban bagus + 1 jelek, lalu complete lesson. */
    private function finishLesson(User $user, Lesson $lesson, bool $pass = true): void
    {
        $sessionId = 'SESS-'.strtoupper(uniqid());
        $answers = $pass
            ? [self::GOOD_ANSWER, self::GOOD_ANSWER, self::GOOD_ANSWER, self::GOOD_ANSWER, 'No, I prefer a different lifestyle.']
            : array_fill(0, 5, 'I do not know how to answer this question.');

        $questions = $lesson->questions()->orderBy('id')->get();

        foreach ($answers as $i => $answer) {
            $this->actingAs($user)
                ->postJson("api/v1/curriculum/lessons/{$lesson->id}/evaluate-question", [
                    'session_id' => $sessionId,
                    'question_id' => $questions[$i]->id,
                    'user_transcript' => $answer,
                ])->assertOk();
        }

        $this->actingAs($user)
            ->postJson("api/v1/curriculum/lessons/{$lesson->id}/complete", [
                'session_id' => $sessionId,
            ])->assertOk();
    }

    public function test_session_endpoint_creates_practice_session_record(): void
    {
        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $this->getJson('api/v1/curriculum/lessons/'.$this->lessons[0]->id.'/session')
            ->assertOk();

        $this->assertDatabaseCount('practice_sessions', 1);
        $this->assertDatabaseHas('practice_sessions', [
            'user_id' => $user->id,
            'lesson_id' => $this->lessons[0]->id,
            'is_passed' => false,
            'total_questions' => 0,
        ]);
    }

    public function test_complete_updates_practice_session_metrics(): void
    {
        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $this->finishLesson($user, $this->lessons[0], true);

        $this->assertDatabaseCount('practice_sessions', 1);
        $this->assertDatabaseHas('practice_sessions', [
            'user_id' => $user->id,
            'lesson_id' => $this->lessons[0]->id,
            'is_passed' => true,
            'total_questions' => 5,
            'correct_count' => 4,
            'score' => 76.4,
            'total_keypoints' => 5,
            'correct_keypoints' => 4,
        ]);
    }

    public function test_predictor_returns_need_more_data_below_three_passed_lessons(): void
    {
        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $this->finishLesson($user, $this->lessons[0], true);

        $this->getJson('api/v1/user/progress-predictor')
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.is_ready', false)
            ->assertJsonPath('data.predicted_band', 'NEED_MORE_DATA')
            ->assertJsonPath('data.passed_lessons_count', 1)
            ->assertJsonPath('data.required_lessons_count', 3)
            ->assertJsonPath('data.overall_index', 0);
    }

    public function test_predictor_ready_after_three_passed_lessons(): void
    {
        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        foreach ([$this->lessons[0], $this->lessons[1], $this->lessons[2]] as $lesson) {
            $this->finishLesson($user, $lesson, true);
        }

        // completion = 3/4 * 100 = 75
        // mastery: rata-rata skor sesi (4*86 + 38) / 5 = 76.40
        // accuracy: 4/5 * 100 = 80.00
        // overall = 75*0.4 + 76.4*0.4 + 80*0.2 = 76.56 → Band 5.5-6.0 (Modest)
        $response = $this->getJson('api/v1/user/progress-predictor')
            ->assertOk()
            ->assertJsonPath('data.is_ready', true)
            ->assertJsonPath('data.predicted_band', 'Band 5.5 - 6.0')
            ->assertJsonPath('data.status_label', 'Modest / Need More Practice')
            ->assertJsonPath('data.color_code', '#EAB308')
            ->assertJsonPath('data.metrics_breakdown.completion_rate.passed_lessons', 3)
            ->assertJsonPath('data.metrics_breakdown.completion_rate.total_lessons', 4)
            ->assertJsonPath('data.metrics_breakdown.completion_rate.score', 75)
            ->assertJsonPath('data.metrics_breakdown.mastery_performance.score', 76.4)
            ->assertJsonPath('data.metrics_breakdown.mastery_performance.based_on_last_sessions', 10)
            ->assertJsonPath('data.metrics_breakdown.key_point_accuracy.score', 80);

        $this->assertSame(76.56, $response->json('data.overall_index'));

        $cache = \App\Models\UserProgressPredictor::where('user_id', $user->id)->first();
        $this->assertNotNull($cache);
        $this->assertTrue($cache->is_ready);
    }

    public function test_inactive_lessons_excluded_from_total_lessons(): void
    {
        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $this->lessons[3]->update(['is_active' => false]);

        foreach ([$this->lessons[0], $this->lessons[1], $this->lessons[2]] as $lesson) {
            $this->finishLesson($user, $lesson, true);
        }

        $this->getJson('api/v1/user/progress-predictor')
            ->assertOk()
            ->assertJsonPath('data.metrics_breakdown.completion_rate.total_lessons', 3)
            ->assertJsonPath('data.metrics_breakdown.completion_rate.passed_lessons', 3)
            ->assertJsonPath('data.metrics_breakdown.completion_rate.score', 100);
    }

    public function test_predictor_requires_authentication(): void
    {
        $this->getJson('api/v1/user/progress-predictor')->assertUnauthorized();
    }
}