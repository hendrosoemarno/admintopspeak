<?php

namespace Tests\Feature\Api;

use App\Enums\SubscriptionStatus;
use App\Models\Lesson;
use App\Models\LlmSetting;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ExamSessionApiTest extends TestCase
{
    use RefreshDatabase;

    private const GOOD_TRANSCRIPT = 'Hello, my name is Budi. I live in the city of Jakarta and I greet my neighbours every morning. My family and I enjoy cooking together on the weekend.';

    private const LONG_TRANSCRIPT = 'I strongly believe that governments around the world should invest more money in public education because education is the foundation of a prosperous and peaceful society. When people have access to good schools and qualified teachers, they can find better jobs, support their families, and contribute to their communities in meaningful ways. I have personally seen how a single scholarship can change the life of a student who would otherwise never have the opportunity to attend a university. In my opinion, education is not just about learning facts from textbooks but also about developing critical thinking, creativity, and empathy for other people. These are the qualities that allow individuals to solve complex problems and build bridges between different cultures and generations. Therefore, I would argue that every child, regardless of where they are born or how much money their parents have, deserves a fair chance to learn and grow into a confident and capable adult who can make the world a better place for everyone.';

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            ['TOEFL_IBT', 2, 'C1', 'TOEFL part two question one.'],
            ['TOEFL_IBT', 2, 'C1', 'TOEFL part two question two.'],
            ['TOEFL_IBT', 4, 'C2', 'TOEFL part four question one.'],
            ['TOEFL_IBT', 4, 'C2', 'TOEFL part four question two.'],
        ] as [$type, $part, $level, $text]) {
            QuestionBank::create([
                'test_type' => $type,
                'part_number' => $part,
                'cefr_level' => $level,
                'question_text' => $text,
                'required_vocab_tags' => ['exam'],
                'is_starter' => false,
                'topic_category' => 'Exam',
                'metadata' => ['audio_url' => null],
            ]);
        }

        // Soal IELTS SPEAKING kini berasal dari kurikulum baru (units/lessons/questions),
        // bukan QuestionBank. Sebanyak 2 soal per part (part 1-3).
        foreach ([1 => 'Part one', 2 => 'Part two', 3 => 'Part three'] as $part => $title) {
            $unit = Unit::create([
                'unit_number' => 100 + $part,
                'title' => "IELTS {$title}",
                'part' => $part,
            ]);

            $lesson = Lesson::create([
                'unit_id' => $unit->id,
                'lesson_number' => 1,
                'title' => "Lesson {$part}",
                'difficulty' => 'Medium',
            ]);

            foreach ([1, 2] as $i) {
                Question::create([
                    'lesson_id' => $lesson->id,
                    'question_text' => "IELTS part {$part} question {$i}.",
                    'model_answer' => 'A model answer for this IELTS question.',
                    'key_point' => "part-{$part}",
                ]);
            }
        }

        $this->fakeLlm();
    }

    private function ieltsQuestions(): \Illuminate\Support\Collection
    {
        return Question::query()
            ->whereHas('lesson.unit', fn ($q) => $q->whereIn('units.part', [1, 2, 3]))
            ->with('lesson.unit')
            ->orderBy('id')
            ->get()
            ->values();
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
                $system = $messages[0]['content'] ?? '';
                $transcript = '';
                foreach ($messages as $m) {
                    if (($m['role'] ?? '') === 'user') {
                        $transcript = $m['content'] ?? '';
                    }
                }

                // Promp evaluator kurikulum IELTS -> JSON rubrik lesson.
                if (str_contains($system, 'IELTS Speaking lesson evaluator')) {
                    $good = str_contains($transcript, self::LONG_TRANSCRIPT);

                    return Http::response([
                        'choices' => [[
                            'message' => ['content' => json_encode([
                                'key_point_detected' => $good,
                                'key_point_score' => $good ? 0.9 : 0.2,
                                'grammar_score' => $good ? 0.85 : 0.6,
                                'lexical_score' => $good ? 0.8 : 0.5,
                                'grammar_feedback' => $good ? 'Sentence structure is correct.' : 'Some minor errors.',
                                'vocabulary_feedback' => $good ? 'Good use of targeted collocations.' : 'Limited vocabulary range.',
                                'suggested_answer' => $good ? '' : 'Yes, my job gives me opportunities for professional development every day.',
                            ])],
                        ]],
                    ], 200);
                }

                return Http::response([
                    'choices' => [[
                        'message' => ['content' => json_encode([
                            'is_correct' => true,
                            'corrected_sentence' => '',
                            'error_description' => '',
                        ])],
                    ]],
                ], 200);
            },
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

    public function test_start_ielts_returns_exam_payload_and_part_sequence(): void
    {
        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $response = $this->postJson('api/v1/sessions/start', ['mode' => 'IELTS_SPEAKING'])
            ->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.mode', 'IELTS_SPEAKING')
            ->assertJsonPath('data.exam.test_type', 'IELTS_SPEAKING')
            ->assertJsonPath('data.exam.parts', [1, 2, 3])
            ->assertJsonPath('data.total_turns_planned', 6)
            ->assertJsonStructure([
                'data' => [
                    'session_id',
                    'exam' => ['test_type', 'parts'],
                    'first_question' => ['question_id', 'turn_number', 'part_number', 'question_text'],
                ],
            ]);

        // Part pertama harus part 1 (urutan part asc).
        $this->assertSame(1, $response->json('data.first_question.part_number'));
    }

    public function test_start_ielts_with_lesson_scopes_exam_to_part_of_that_lesson(): void
    {
        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $lesson = Lesson::whereHas('unit', fn ($q) => $q->where('units.part', 2))->firstOrFail();
        $lessonQuestionIds = Question::where('lesson_id', $lesson->id)->pluck('id');

        $response = $this->postJson('api/v1/sessions/start', [
            'mode' => 'IELTS_SPEAKING',
            'lesson_id' => $lesson->id,
        ])->assertCreated()
            ->assertJsonPath('data.exam.parts', [2])
            ->assertJsonPath('data.total_turns_planned', 2)
            ->assertJsonPath('data.first_question.part_number', 2);

        // Soal pertama harus berasal dari lesson yang dipilih.
        $this->assertContains($response->json('data.first_question.question_id'), $lessonQuestionIds->all());
    }

    public function test_start_toefl_returns_parts_in_ascending_order(): void
    {
        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $this->postJson('api/v1/sessions/start', ['mode' => 'TOEFL_IBT'])
            ->assertCreated()
            ->assertJsonPath('data.exam.parts', [2, 4])
            ->assertJsonPath('data.total_turns_planned', 4)
            ->assertJsonPath('data.first_question.part_number', 2);
    }

    public function test_exam_question_selection_follows_part_sequence(): void
    {
        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $sessionId = $this->postJson('api/v1/sessions/start', ['mode' => 'IELTS_SPEAKING'])
            ->json('data.session_id');

        // Turn 1 berasal dari part 1.
        $q1 = $this->ieltsQuestions()->where('lesson.unit.part', 1)->first()->id;

        $nextAfterTurn1 = $this->postJson('api/v1/sessions/evaluate-turn', [
            'session_id' => $sessionId,
            'turn_number' => 1,
            'question_id' => $q1,
            'user_transcript' => self::LONG_TRANSCRIPT,
        ])->assertOk()
            ->assertJsonPath('data.step_state', 'NORMAL')
            ->json('data.next_question');

        // Turn 2 masih part 1.
        $this->assertSame(1, $nextAfterTurn1['part_number']);

        $nextAfterTurn2 = $this->postJson('api/v1/sessions/evaluate-turn', [
            'session_id' => $sessionId,
            'turn_number' => 2,
            'question_id' => $nextAfterTurn1['question_id'],
            'user_transcript' => self::LONG_TRANSCRIPT,
        ])->assertOk()
            ->assertJsonPath('data.step_state', 'NORMAL')
            ->json('data.next_question');

        // Turn 3 lanjut ke part 2 (urutan part naik).
        $this->assertSame(2, $nextAfterTurn2['part_number']);
    }

    public function test_exam_complete_returns_exam_report_without_promotion(): void
    {
        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $sessionId = $this->postJson('api/v1/sessions/start', ['mode' => 'IELTS_SPEAKING'])
            ->json('data.session_id');

        // 6 turn benar (2 per part untuk part 1-3).
        $questions = $this->ieltsQuestions();

        foreach ([1, 2, 3, 4, 5, 6] as $turn) {
            $this->postJson('api/v1/sessions/evaluate-turn', [
                'session_id' => $sessionId,
                'turn_number' => $turn,
                'question_id' => $questions[$turn - 1]->id,
                'user_transcript' => self::LONG_TRANSCRIPT,
            ])->assertOk()->assertJsonPath('data.has_error', false);
        }

        $user->refresh();
        $levelBefore = $user->current_cefr_level->value;

        $this->postJson('api/v1/sessions/complete', ['session_id' => $sessionId])
            ->assertOk()
            ->assertJsonPath('data.is_promoted', false)
            ->assertJsonPath('data.previous_cefr_level', null)
            ->assertJsonPath('data.new_cefr_level', null)
            ->assertJsonPath('data.accumulated_score', 516)
            ->assertJsonStructure([
                'data' => [
                    'exam_report' => [
                        'test_type',
                        'total_score',
                        'max_score',
                        'score_pct',
                        'grammar_accuracy',
                        'parts',
                    ],
                    'diagnostic_report' => ['grammar_accuracy', 'frequent_errors', 'tutor_notes'],
                ],
            ]);

        // Tidak ada promosi: level user tidak berubah.
        $user->refresh();
        $this->assertSame($levelBefore, $user->current_cefr_level->value);
    }

    public function test_exam_report_breaks_down_by_part(): void
    {
        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $sessionId = $this->postJson('api/v1/sessions/start', ['mode' => 'IELTS_SPEAKING'])
            ->json('data.session_id');

        $questions = $this->ieltsQuestions();

        // Part 1: dua-duanya benar, Part 2: dua-duanya salah, Part 3: benar.
        foreach ([1, 2] as $turn) {
            $this->postJson('api/v1/sessions/evaluate-turn', [
                'session_id' => $sessionId,
                'turn_number' => $turn,
                'question_id' => $questions[$turn - 1]->id,
                'user_transcript' => self::LONG_TRANSCRIPT,
            ])->assertOk();
        }
        foreach ([3, 4] as $turn) {
            $this->postJson('api/v1/sessions/evaluate-turn', [
                'session_id' => $sessionId,
                'turn_number' => $turn,
                'question_id' => $questions[$turn - 1]->id,
                'user_transcript' => 'I do not know.',
            ])->assertOk()->assertJsonPath('data.has_error', true);
        }
        foreach ([5, 6] as $turn) {
            $this->postJson('api/v1/sessions/evaluate-turn', [
                'session_id' => $sessionId,
                'turn_number' => $turn,
                'question_id' => $questions[$turn - 1]->id,
                'user_transcript' => self::LONG_TRANSCRIPT,
            ])->assertOk();
        }

        $json = $this->postJson('api/v1/sessions/complete', ['session_id' => $sessionId])
            ->assertOk()
            ->json();

        // IELTS memakai skor kurikulum 0-100 per turn; jawaban salah tetap
        // mendapat skor parsial (38/100), benar 86/100.
        $this->assertSame(420, $json['data']['exam_report']['total_score']);
        $this->assertSame(600, $json['data']['exam_report']['max_score']);
        $this->assertSame(70, $json['data']['exam_report']['score_pct']);
        $this->assertSame(1, $json['data']['exam_report']['parts'][0]['part_number']);
        $this->assertSame(172, $json['data']['exam_report']['parts'][0]['total_score']);
        $this->assertSame(2, $json['data']['exam_report']['parts'][1]['part_number']);
        $this->assertSame(76, $json['data']['exam_report']['parts'][1]['total_score']);
        $this->assertSame(3, $json['data']['exam_report']['parts'][2]['part_number']);
        $this->assertSame(172, $json['data']['exam_report']['parts'][2]['total_score']);
    }

    public function test_exam_session_appears_in_history_with_test_type(): void
    {
        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $sessionId = $this->postJson('api/v1/sessions/start', ['mode' => 'TOEFL_IBT'])
            ->json('data.session_id');

        $questionId = QuestionBank::where('test_type', 'TOEFL_IBT')->where('part_number', 2)->first()->id;

        $this->postJson('api/v1/sessions/evaluate-turn', [
            'session_id' => $sessionId,
            'turn_number' => 1,
            'question_id' => $questionId,
            'user_transcript' => self::LONG_TRANSCRIPT,
        ])->assertOk();

        $this->postJson('api/v1/sessions/complete', ['session_id' => $sessionId])->assertOk();

        $this->getJson('api/v1/sessions/history')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.items.0.mode', 'TOEFL_IBT')
            ->assertJsonPath('data.items.0.test_type', 'TOEFL_IBT');
    }

    public function test_ielts_evaluate_returns_suggested_answer_only_when_not_correct(): void
    {
        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $sessionId = $this->postJson('api/v1/sessions/start', ['mode' => 'IELTS_SPEAKING'])
            ->json('data.session_id');

        $q1 = $this->ieltsQuestions()->first()->id;

        // Jawaban salah -> saran jawaban benar diisi di payload dan database.
        $wrong = $this->postJson('api/v1/sessions/evaluate-turn', [
            'session_id' => $sessionId,
            'turn_number' => 1,
            'question_id' => $q1,
            'user_transcript' => 'I do not know.',
        ])->assertOk()
            ->assertJsonPath('data.has_error', true)
            ->assertJsonPath('data.scores.suggested_answer', 'Yes, my job gives me opportunities for professional development every day.');

        $this->assertDatabaseHas('conversation_logs', [
            'user_id' => $user->id,
            'session_id' => $sessionId,
            'turn_number' => 1,
            'suggested_answer' => 'Yes, my job gives me opportunities for professional development every day.',
        ]);

        // Jawaban benar -> saran jawaban kosong.
        $nextQuestion = $wrong->json('data.next_question');

        $this->postJson('api/v1/sessions/evaluate-turn', [
            'session_id' => $sessionId,
            'turn_number' => 2,
            'question_id' => $nextQuestion['question_id'],
            'user_transcript' => self::LONG_TRANSCRIPT,
        ])->assertOk()
            ->assertJsonPath('data.has_error', false)
            ->assertJsonPath('data.scores.suggested_answer', '');
    }

    public function test_ielts_evaluator_prompt_ignores_capitalization_and_asks_for_suggested_answer(): void
    {
        $prompt = file_get_contents(resource_path('prompts/ielts_lesson_evaluator.txt'));

        $this->assertStringContainsString('Ignore capitalization entirely', $prompt);
        $this->assertStringContainsString('never penalize grammar_score or lexical_score for case mistakes', $prompt);
        $this->assertStringContainsString('"suggested_answer"', $prompt);
        $this->assertStringContainsString('already correct, suggested_answer must be an empty string', $prompt);
    }

    public function test_invalid_exam_mode_still_rejected_by_request(): void
    {
        $user = $this->premiumUser();
        Sanctum::actingAs($user);

        $this->postJson('api/v1/sessions/start', ['mode' => 'FCE'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('mode');
    }
}
