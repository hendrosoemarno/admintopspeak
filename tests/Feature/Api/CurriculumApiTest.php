<?php

namespace Tests\Feature\Api;

use App\Enums\LessonProgressStatus;
use App\Enums\SubscriptionStatus;
use App\Models\CurriculumEvaluationLog;
use App\Models\Lesson;
use App\Models\LlmSetting;
use App\Models\Question;
use App\Models\Unit;
use App\Models\UserLessonProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CurriculumApiTest extends TestCase
{
    use RefreshDatabase;

    private const GOOD_ANSWER = 'My hometown is a small coastal city in Java, and I live in a close-knit family community.';

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

        $this->lesson1 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 1,
            'title' => 'Hometown & Living Place',
            'difficulty' => 'Easy',
        ]);

        $this->lesson2 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 2,
            'title' => 'Family Members & Relationships',
            'difficulty' => 'Medium',
        ]);

        foreach ([$this->lesson1, $this->lesson2] as $lesson) {
            foreach (range(1, 5) as $i) {
                Question::create([
                    'lesson_id' => $lesson->id,
                    'question_text' => "Question {$i} about home and family life for lesson {$lesson->id}?",
                    'model_answer' => 'Model answer describing a close-knit family living in a residential area.',
                    'key_point' => 'close-knit family',
                ]);
            }
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
                $messages = data_get($request->data(), 'messages', []);
                $transcript = '';
                foreach ($messages as $m) {
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
                            'grammar_feedback' => $good ? 'Sentence structure is correct.' : 'Some minor errors.',
                            'vocabulary_feedback' => $good ? 'Good use of targeted collocations.' : 'Limited vocabulary range.',
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
            'current_cefr_level' => 'B1',
            'remaining_trial_sessions' => 5,
            'subscription_status' => SubscriptionStatus::PREMIUM_MONTHLY,
        ]);
    }

    private function freeUser(): User
    {
        return User::factory()->create([
            'current_cefr_level' => 'B1',
            'remaining_trial_sessions' => 1,
            'total_free_sessions_granted' => 1,
            'subscription_status' => SubscriptionStatus::FREE,
        ]);
    }

    /**
     * Helper: submit 5 jawaban 1 per 1 via evaluate-question.
     */
    private function submitAnswers(User $user, Lesson $lesson, string $sessionId, array $transcripts): void
    {
        $questions = $lesson->questions()->orderBy('id')->get();

        foreach ($transcripts as $i => $transcript) {
            $this->actingAs($user)
                ->postJson("api/v1/curriculum/lessons/{$lesson->id}/evaluate-question", [
                    'session_id' => $sessionId,
                    'question_id' => $questions[$i]->id,
                    'user_transcript' => $transcript,
                ])->assertOk();
        }
    }

    // ──────────────────────────────────────────────────────────────
    // curriculum & session (tidak diubah)
    // ──────────────────────────────────────────────────────────────

    public function test_curriculum_returns_units_with_default_not_passed_status(): void
    {
        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $this->getJson('api/v1/curriculum')
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'data' => [[
                    'unit_id',
                    'unit_number',
                    'unit_title',
                    'part',
                    'outcome',
                    'lessons' => [[
                        'lesson_id',
                        'lesson_number',
                        'lesson_title',
                        'difficulty',
                        'status',
                    ]],
                ]],
            ])
            ->assertJsonPath('data.0.unit_title', 'Home & Family')
            ->assertJsonPath('data.0.lessons.0.lesson_title', 'Hometown & Living Place')
            ->assertJsonPath('data.0.lessons.0.status', 'NOT_PASSED');
    }

    public function test_session_returns_five_random_questions(): void
    {
        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $this->getJson('api/v1/curriculum/lessons/'.$this->lesson1->id.'/session')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'session_id',
                    'lesson_id',
                    'questions' => [[
                        'question_id',
                        'question_text',
                        'key_point',
                    ]],
                ],
            ])
            ->assertJsonCount(5, 'data.questions')
            ->assertJsonPath('data.lesson_id', $this->lesson1->id);
    }

    public function test_session_returns_404_for_missing_lesson(): void
    {
        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $this->getJson('api/v1/curriculum/lessons/99999/session')
            ->assertStatus(404);
    }

    public function test_curriculum_reflects_passed_status(): void
    {
        $user = $this->premiumUser();

        UserLessonProgress::create([
            'user_id' => $user->id,
            'lesson_id' => $this->lesson1->id,
            'status' => LessonProgressStatus::PASSED,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('api/v1/curriculum')
            ->assertOk()
            ->assertJsonPath('data.0.lessons.0.status', 'PASSED')
            ->assertJsonPath('data.0.lessons.1.status', 'NOT_PASSED');
    }

    // ──────────────────────────────────────────────────────────────
    // evaluate-question: happy path
    // ──────────────────────────────────────────────────────────────

    public function test_evaluate_question_stores_single_answer(): void
    {
        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $question = $this->lesson1->questions()->first();

        $this->postJson('api/v1/curriculum/lessons/'.$this->lesson1->id.'/evaluate-question', [
            'session_id' => 'SESS-TEST-01',
            'question_id' => $question->id,
            'user_transcript' => self::GOOD_ANSWER,
        ])->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'question_id',
                    'is_correct',
                    'score',
                    'key_point_detected',
                    'key_point_target',
                    'grammar_feedback',
                    'vocabulary_feedback',
                    'suggested_answer',
                ],
            ])
            ->assertJsonPath('data.is_correct', true)
            ->assertJsonPath('data.score', 86);

        $this->assertDatabaseHas('curriculum_evaluation_logs', [
            'user_id' => $user->id,
            'session_id' => 'SESS-TEST-01',
            'question_id' => $question->id,
            'is_correct' => true,
        ]);
    }

    public function test_evaluate_question_returns_suggested_answer_when_incorrect(): void
    {
        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $question = $this->lesson1->questions()->first();

        $this->postJson('api/v1/curriculum/lessons/'.$this->lesson1->id.'/evaluate-question', [
            'session_id' => 'SESS-TEST-02',
            'question_id' => $question->id,
            'user_transcript' => 'I do not know.',
        ])->assertOk()
            ->assertJsonPath('data.is_correct', false)
            ->assertJsonPath('data.suggested_answer', 'Yes, my close-knit family lives in a small coastal city.');

        $this->assertDatabaseHas('curriculum_evaluation_logs', [
            'session_id' => 'SESS-TEST-02',
            'question_id' => $question->id,
            'suggested_answer' => 'Yes, my close-knit family lives in a small coastal city.',
        ]);
    }

    public function test_complete_passes_when_four_out_of_five_correct(): void
    {
        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $sessionId = 'SESS-COMPLETE-01';
        $this->submitAnswers($user, $this->lesson1, $sessionId, [
            self::GOOD_ANSWER,
            self::GOOD_ANSWER,
            self::GOOD_ANSWER,
            self::GOOD_ANSWER,
            'No, I prefer a different lifestyle.',
        ]);

        $this->actingAs($user)
            ->postJson('api/v1/curriculum/lessons/'.$this->lesson1->id.'/complete', [
                'session_id' => $sessionId,
            ])->assertOk()
            ->assertJsonPath('data.session_result.correct_count', 4)
            ->assertJsonPath('data.session_result.total_questions', 5)
            ->assertJsonPath('data.session_result.is_passed', true)
            ->assertJsonPath('data.session_result.lesson_status', 'PASSED')
            ->assertJsonCount(5, 'data.evaluations');

        $this->assertDatabaseHas('user_lesson_progress', [
            'user_id' => $user->id,
            'lesson_id' => $this->lesson1->id,
            'status' => LessonProgressStatus::PASSED->value,
        ]);
    }

    public function test_complete_stays_not_passed_when_less_than_four_correct(): void
    {
        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $sessionId = 'SESS-COMPLETE-02';
        $this->submitAnswers($user, $this->lesson1, $sessionId, [
            self::GOOD_ANSWER,
            self::GOOD_ANSWER,
            'I do not know how to answer this question.',
            'I do not know how to answer this question.',
            'I do not know how to answer this question.',
        ]);

        $this->actingAs($user)
            ->postJson('api/v1/curriculum/lessons/'.$this->lesson1->id.'/complete', [
                'session_id' => $sessionId,
            ])->assertOk()
            ->assertJsonPath('data.session_result.is_passed', false)
            ->assertJsonPath('data.session_result.lesson_status', 'NOT_PASSED');

        $this->assertDatabaseHas('user_lesson_progress', [
            'user_id' => $user->id,
            'lesson_id' => $this->lesson1->id,
            'status' => LessonProgressStatus::NOT_PASSED->value,
        ]);
    }

    public function test_passed_status_is_never_downgraded(): void
    {
        $user = $this->premiumUser();

        UserLessonProgress::create([
            'user_id' => $user->id,
            'lesson_id' => $this->lesson1->id,
            'status' => LessonProgressStatus::PASSED,
        ]);

        Sanctum::actingAs($user);

        $sessionId = 'SESS-COMPLETE-03';
        $this->submitAnswers($user, $this->lesson1, $sessionId, [
            'I do not know how to answer this question.',
            'I do not know how to answer this question.',
            'I do not know how to answer this question.',
            'I do not know how to answer this question.',
            'I do not know how to answer this question.',
        ]);

        $this->actingAs($user)
            ->postJson('api/v1/curriculum/lessons/'.$this->lesson1->id.'/complete', [
                'session_id' => $sessionId,
            ])->assertOk()
            ->assertJsonPath('data.session_result.is_passed', false);

        // Status PASSED yang sudah tercapai tidak boleh turun.
        $this->assertDatabaseHas('user_lesson_progress', [
            'user_id' => $user->id,
            'lesson_id' => $this->lesson1->id,
            'status' => LessonProgressStatus::PASSED->value,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // evaluate-question: validasi
    // ──────────────────────────────────────────────────────────────

    public function test_evaluate_question_rejects_duplicate(): void
    {
        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $question = $this->lesson1->questions()->first();

        $payload = [
            'session_id' => 'SESS-DUP-01',
            'question_id' => $question->id,
            'user_transcript' => self::GOOD_ANSWER,
        ];

        $this->postJson('api/v1/curriculum/lessons/'.$this->lesson1->id.'/evaluate-question', $payload)
            ->assertOk();

        $this->postJson('api/v1/curriculum/lessons/'.$this->lesson1->id.'/evaluate-question', $payload)
            ->assertStatus(422);
    }

    public function test_evaluate_question_rejects_question_not_belonging_to_lesson(): void
    {
        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $foreignQuestionId = $this->lesson2->questions()->first()->id;

        $this->postJson('api/v1/curriculum/lessons/'.$this->lesson1->id.'/evaluate-question', [
            'session_id' => 'SESS-FOREIGN-01',
            'question_id' => $foreignQuestionId,
            'user_transcript' => self::GOOD_ANSWER,
        ])->assertStatus(422);
    }

    public function test_evaluate_question_returns_404_for_missing_lesson(): void
    {
        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $this->postJson('api/v1/curriculum/lessons/99999/evaluate-question', [
            'session_id' => 'SESS-MISS-01',
            'question_id' => 1,
            'user_transcript' => self::GOOD_ANSWER,
        ])->assertStatus(404);
    }

    public function test_evaluate_question_returns_422_when_llm_disabled(): void
    {
        config(['llm.api_key' => '']);
        LlmSetting::current()->update(['is_enabled' => false, 'api_key' => null]);

        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $questionId = $this->lesson1->questions()->value('id');

        $this->postJson('api/v1/curriculum/lessons/'.$this->lesson1->id.'/evaluate-question', [
            'session_id' => 'SESS-NO-LLM-01',
            'question_id' => $questionId,
            'user_transcript' => self::GOOD_ANSWER,
        ])->assertStatus(422);
    }

    public function test_evaluate_question_rejects_missing_session_id(): void
    {
        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $questionId = $this->lesson1->questions()->value('id');

        $this->postJson('api/v1/curriculum/lessons/'.$this->lesson1->id.'/evaluate-question', [
            'question_id' => $questionId,
            'user_transcript' => self::GOOD_ANSWER,
        ])->assertStatus(422)
            ->assertJsonValidationErrors('session_id');
    }

    public function test_evaluate_question_rejects_missing_transcript(): void
    {
        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $questionId = $this->lesson1->questions()->value('id');

        $this->postJson('api/v1/curriculum/lessons/'.$this->lesson1->id.'/evaluate-question', [
            'session_id' => 'SESS-EMPTY-01',
            'question_id' => $questionId,
        ])->assertStatus(422)
            ->assertJsonValidationErrors('user_transcript');
    }

    // ──────────────────────────────────────────────────────────────
    // complete: validasi
    // ──────────────────────────────────────────────────────────────

    public function test_complete_rejects_empty_session(): void
    {
        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $this->postJson('api/v1/curriculum/lessons/'.$this->lesson1->id.'/complete', [
            'session_id' => 'SESS-EMPTY-COMPLETE',
        ])->assertStatus(422);
    }

    public function test_complete_returns_404_for_missing_lesson(): void
    {
        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $this->postJson('api/v1/curriculum/lessons/99999/complete', [
            'session_id' => 'SESS-MISS-COMPLETE',
        ])->assertStatus(404);
    }

    public function test_complete_consumes_free_quota_once_and_returns_remaining(): void
    {
        $user = $this->freeUser();
        Sanctum::actingAs($user);

        $sessionId = 'SESS-FREE-01';
        $this->submitAnswers($user, $this->lesson1, $sessionId, [
            self::GOOD_ANSWER,
            self::GOOD_ANSWER,
            'I do not know how to answer this question.',
            'I do not know how to answer this question.',
            'I do not know how to answer this question.',
        ]);

        $this->actingAs($user)
            ->postJson('api/v1/curriculum/lessons/'.$this->lesson1->id.'/complete', [
                'session_id' => $sessionId,
            ])->assertOk()
            ->assertJsonPath('data.remaining_trial_sessions', 0);

        $this->assertSame(0, $user->fresh()->remaining_trial_sessions);

        // complete kedua untuk session yang sama tidak mengonsumsi kuota lagi.
        $this->actingAs($user)
            ->postJson('api/v1/curriculum/lessons/'.$this->lesson1->id.'/complete', [
                'session_id' => $sessionId,
            ])->assertOk()
            ->assertJsonPath('data.remaining_trial_sessions', 0);

        $this->assertSame(0, $user->fresh()->remaining_trial_sessions);
    }

    public function test_complete_does_not_consume_quota_for_premium_user(): void
    {
        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $sessionId = 'SESS-PREMIUM-01';
        $this->submitAnswers($user, $this->lesson1, $sessionId, [
            self::GOOD_ANSWER,
            self::GOOD_ANSWER,
            self::GOOD_ANSWER,
            self::GOOD_ANSWER,
            self::GOOD_ANSWER,
        ]);

        $this->actingAs($user)
            ->postJson('api/v1/curriculum/lessons/'.$this->lesson1->id.'/complete', [
                'session_id' => $sessionId,
            ])->assertOk()
            ->assertJsonPath('data.remaining_trial_sessions', 5);

        $this->assertSame(5, $user->fresh()->remaining_trial_sessions);
    }

    // ──────────────────────────────────────────────────────────────
    // auth
    // ──────────────────────────────────────────────────────────────

    public function test_curriculum_endpoints_require_authentication(): void
    {
        $this->getJson('api/v1/curriculum')->assertStatus(401);
        $this->getJson('api/v1/curriculum/lessons/1/session')->assertStatus(401);
        $this->postJson('api/v1/curriculum/lessons/1/evaluate-question', [
            'session_id' => 'X',
            'question_id' => 1,
            'user_transcript' => 'x',
        ])->assertStatus(401);
        $this->postJson('api/v1/curriculum/lessons/1/complete', [
            'session_id' => 'X',
        ])->assertStatus(401);
    }
}