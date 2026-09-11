<?php

namespace Tests\Feature\Api;

use App\Enums\SubscriptionStatus;
use App\Models\GrammarRule;
use App\Models\LlmSetting;
use App\Models\QuestionBank;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SessionApiTest extends TestCase
{
    use RefreshDatabase;

    private const GOOD_TRANSCRIPT = 'Hello, my name is Budi. I live in the city of Jakarta and I greet my neighbours every morning. My family and I enjoy cooking together on the weekend.';

    protected function setUp(): void
    {
        parent::setUp();

        GrammarRule::create([
            'rule_code' => 'TENSE_01',
            'category' => 'Past Tense',
            'cefr_level' => 'A2',
            'regex_pattern' => '/\byesterday\s+.+\b(go|buy|see|eat)\b/i',
            'description' => 'On past context the verb must change form (went, bought, saw, ate).',
            'is_active' => true,
        ]);

        foreach (range(1, 5) as $i) {
            QuestionBank::create([
                'test_type' => 'ADAPTIVE',
                'part_number' => 1,
                'cefr_level' => 'A1',
                'question_text' => "Starter question number {$i} about your daily routine please.",
                'required_vocab_tags' => ['name', 'greet', 'city'],
                'is_starter' => $i === 1,
                'topic_category' => 'Daily Routine',
                'metadata' => ['audio_url' => null],
            ]);
        }

        // Level berikutnya (A2) untuk verifikasi promosi.
        QuestionBank::create([
            'test_type' => 'ADAPTIVE',
            'part_number' => 1,
            'cefr_level' => 'A2',
            'question_text' => 'Describe how you relax after a long day at work please.',
            'required_vocab_tags' => ['relax', 'family'],
            'is_starter' => false,
            'topic_category' => 'Daily Routine',
            'metadata' => ['audio_url' => null],
        ]);

        $this->fakeLlm();
    }

    /**
     * Aktifkan LLM dengan respons palsu: transkrip GOOD dianggap benar,
     * sisanya dianggap salah dan diberikan koreksi.
     */
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
                $content = '';
                foreach ($messages as $m) {
                    if (($m['role'] ?? '') === 'user') {
                        $content = $m['content'] ?? '';
                    }
                }
                // Ambil kalimat user dari prompt "Learner sentence: \"...\"".
                if (preg_match('/Learner sentence: "([^"]*)"/', $content, $m)) {
                    $sentence = $m[1];
                } else {
                    $sentence = $content;
                }

                return Http::response([
                    'choices' => [[
                        'message' => ['content' => $this->llmEvalPayload($sentence)],
                    ]],
                ], 200);
            },
        ]);
    }

    private function llmEvalPayload(string $sentence): string
    {
        // Kasus-kasus kesalahan yang dikenal -> LLM menandai salah + memberi koreksi.
        $errorMap = [
            'Yesterday I go to the market.' => 'Yesterday I went to the market.',
            'I go to Bandung last year.' => 'I went to Bandung last year.',
            'my hobby in free time is work, because i love work' => 'My hobby in my free time is working because I love my job.',
        ];

        foreach ($errorMap as $wrong => $correct) {
            if (str_contains($sentence, $wrong)) {
                return json_encode([
                    'is_correct' => false,
                    'corrected_sentence' => str_replace($wrong, $correct, $sentence),
                    'error_description' => 'Ada kesalahan grammar dalam kalimat.',
                ]);
            }
        }

        return json_encode([
            'is_correct' => true,
            'corrected_sentence' => '',
            'error_description' => '',
        ]);
    }

    private function freeUser(): User
    {
        return User::factory()->create([
            'current_cefr_level' => 'A1',
            'remaining_trial_sessions' => 1,
            'subscription_status' => SubscriptionStatus::FREE,
        ]);
    }

    public function test_start_session_returns_first_question(): void
    {
        $user = $this->freeUser();
        Sanctum::actingAs($user);

        $this->postJson('api/v1/sessions/start', ['mode' => 'ADAPTIVE'])
            ->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.current_cefr_level', 'A1')
            ->assertJsonPath('data.total_turns_planned', 5)
            ->assertJsonStructure([
                'data' => [
                    'session_id',
                    'first_question' => ['question_id', 'turn_number', 'question_text', 'audio_url'],
                ],
            ]);
    }

    public function test_start_session_uses_starter_question_first(): void
    {
        $user = $this->freeUser();
        Sanctum::actingAs($user);

        $starter = QuestionBank::where('cefr_level', 'A1')->where('is_starter', true)->first();
        $this->assertNotNull($starter);

        $response = $this->postJson('api/v1/sessions/start', ['mode' => 'ADAPTIVE'])
            ->assertCreated();

        $this->assertSame(
            $starter->id,
            $response->json('data.first_question.question_id'),
            'Pertanyaan pertama harus merupakan is_starter = true.',
        );
        $this->assertSame(1, $response->json('data.first_question.turn_number'));
    }

    public function test_subsequent_turns_do_not_force_starter(): void
    {
        $user = $this->freeUser();
        Sanctum::actingAs($user);

        $sessionId = $this->postJson('api/v1/sessions/start', ['mode' => 'ADAPTIVE'])
            ->json('data.session_id');

        // Turn 1 benar (LLM) -> NORMAL, next_question langsung dari evaluate-turn.
        $firstNext = $this->postJson('api/v1/sessions/evaluate-turn', [
            'session_id' => $sessionId,
            'turn_number' => 1,
            'question_id' => QuestionBank::where('cefr_level', 'A1')->first()->id,
            'user_transcript' => self::GOOD_TRANSCRIPT,
        ])->assertOk()
            ->assertJsonPath('data.step_state', 'NORMAL')
            ->json('data.next_question.question_id');

        $this->assertNotContains($firstNext, [null]);

        // Turn 2 -> next_question (turn 3) tidak boleh sama dengan soal terpakai.
        $usedIds = [QuestionBank::where('cefr_level', 'A1')->first()->id, $firstNext];

        $nextId = $this->postJson('api/v1/sessions/evaluate-turn', [
            'session_id' => $sessionId,
            'turn_number' => 2,
            'question_id' => $firstNext,
            'user_transcript' => self::GOOD_TRANSCRIPT,
        ])->assertOk()
            ->json('data.next_question.question_id');

        $this->assertNotContains($nextId, $usedIds);
    }

    public function test_start_session_is_paywalled_when_quota_empty(): void
    {
        $user = User::factory()->create([
            'current_cefr_level' => 'A1',
            'remaining_trial_sessions' => 0,
            'subscription_status' => SubscriptionStatus::FREE,
        ]);
        Sanctum::actingAs($user);

        $this->postJson('api/v1/sessions/start', ['mode' => 'ADAPTIVE'])
            ->assertStatus(402)
            ->assertJsonPath('errors.is_paywalled', true);
    }

    public function test_evaluate_turn_with_error_locks_repetition(): void
    {
        $user = $this->freeUser();
        Sanctum::actingAs($user);

        $sessionId = $this->postJson('api/v1/sessions/start', ['mode' => 'ADAPTIVE'])
            ->json('data.session_id');

        $questionId = QuestionBank::where('cefr_level', 'A1')->first()->id;

        $this->postJson('api/v1/sessions/evaluate-turn', [
            'session_id' => $sessionId,
            'turn_number' => 1,
            'question_id' => $questionId,
            'user_transcript' => 'Yesterday I go to the market.',
        ])->assertOk()
            ->assertJsonPath('data.step_state', 'WAITING_REPETITION')
            ->assertJsonPath('data.has_error', true)
            ->assertJsonPath('data.expected_repetition_text', 'Yesterday I went to the market.')
            ->assertJsonPath('data.next_question', null);

        // Kuota konsumsi di turn pertama.
        $user->refresh();
        $this->assertSame(0, $user->remaining_trial_sessions);
    }

    public function test_grammar_score_is_zero_when_there_is_one_violation(): void
    {
        $user = $this->freeUser();
        Sanctum::actingAs($user);

        $sessionId = $this->postJson('api/v1/sessions/start', ['mode' => 'ADAPTIVE'])
            ->json('data.session_id');

        $questionId = QuestionBank::where('cefr_level', 'A1')->first()->id;

        // "Yesterday I go..." memicu TENSE_01 -> 1 violation -> grammar_score harus 0.
        $this->postJson('api/v1/sessions/evaluate-turn', [
            'session_id' => $sessionId,
            'turn_number' => 1,
            'question_id' => $questionId,
            'user_transcript' => 'Yesterday I go to the market.',
        ])->assertOk()
            ->assertJsonPath('data.has_error', true)
            ->assertJsonPath('data.scores.grammar_score', 0);
    }

    public function test_correct_way_uses_llm_correction(): void
    {
        $question = QuestionBank::where('cefr_level', 'A1')->first();

        $user = $this->freeUser();
        Sanctum::actingAs($user);

        $sessionId = $this->postJson('api/v1/sessions/start', ['mode' => 'ADAPTIVE'])
            ->json('data.session_id');

        // Transkrip salah -> LLM menandai error dan memberi versi koreksi.
        $this->postJson('api/v1/sessions/evaluate-turn', [
            'session_id' => $sessionId,
            'turn_number' => 1,
            'question_id' => $question->id,
            'user_transcript' => 'my hobby in free time is work, because i love work',
        ])->assertOk()
            ->assertJsonPath('data.step_state', 'WAITING_REPETITION')
            ->assertJsonPath('data.has_error', true)
            ->assertJsonPath('data.correction_data.correct_way', 'My hobby in my free time is working because I love my job.')
            ->assertJsonPath('data.expected_repetition_text', 'My hobby in my free time is working because I love my job.');
    }

    public function test_evaluate_turn_correct_sentence_scores_grammar_one(): void
    {
        $user = $this->freeUser();
        Sanctum::actingAs($user);

        $sessionId = $this->postJson('api/v1/sessions/start', ['mode' => 'ADAPTIVE'])
            ->json('data.session_id');

        $questionId = QuestionBank::where('cefr_level', 'A1')->first()->id;

        // GOOD_TRANSCRIPT dinilai benar oleh LLM -> grammar_score 1, NORMAL,
        // dan tidak ada pending rule (penilaian langsung oleh LLM).
        $this->postJson('api/v1/sessions/evaluate-turn', [
            'session_id' => $sessionId,
            'turn_number' => 1,
            'question_id' => $questionId,
            'user_transcript' => self::GOOD_TRANSCRIPT,
        ])->assertOk()
            ->assertJsonPath('data.step_state', 'NORMAL')
            ->assertJsonPath('data.has_error', false)
            ->assertJsonPath('data.scores.grammar_score', 1);

        $this->assertDatabaseMissing('pending_grammar_rules', [
            'raw_user_input' => self::GOOD_TRANSCRIPT,
        ]);
    }

    public function test_verify_repetition_success_moves_forward(): void
    {
        $user = $this->freeUser();
        Sanctum::actingAs($user);

        $sessionId = $this->postJson('api/v1/sessions/start', ['mode' => 'ADAPTIVE'])
            ->json('data.session_id');

        $questionId = QuestionBank::where('cefr_level', 'A1')->first()->id;

        $this->postJson('api/v1/sessions/evaluate-turn', [
            'session_id' => $sessionId,
            'turn_number' => 1,
            'question_id' => $questionId,
            'user_transcript' => 'Yesterday I go to the market.',
        ])->assertOk();

        $this->postJson('api/v1/sessions/verify-repetition', [
            'session_id' => $sessionId,
            'turn_number' => 1,
            'user_repetition_transcript' => 'Yesterday I went to the market.',
        ])->assertOk()
            ->assertJsonPath('data.repetition_success', true)
            ->assertJsonPath('data.step_state', 'NORMAL')
            ->assertJsonPath('data.next_question.turn_number', 2);
    }

    public function test_next_question_never_repeats_current_question(): void
    {
        $user = $this->freeUser();
        $user->update(['remaining_trial_sessions' => 5]);
        Sanctum::actingAs($user);

        $sessionId = $this->postJson('api/v1/sessions/start', ['mode' => 'ADAPTIVE'])
            ->json('data.session_id');

        $seenIds = [];
        $questionId = QuestionBank::where('cefr_level', 'A1')->first()->id;

        // Setiap turn benar (LLM) -> next_question langsung dari evaluate-turn.
        foreach ([1, 2, 3] as $turn) {
            $response = $this->postJson('api/v1/sessions/evaluate-turn', [
                'session_id' => $sessionId,
                'turn_number' => $turn,
                'question_id' => $questionId,
                'user_transcript' => self::GOOD_TRANSCRIPT,
            ])->assertOk();

            $seenIds[] = $questionId;

            $response->assertJsonPath('data.has_error', false);

            $next = $response->json('data.next_question');

            if ($next === null) {
                break;
            }

            $this->assertNotContains($next['question_id'] ?? null, $seenIds, "Turn {$turn} mengulang pertanyaan yang sudah ditanyakan.");
            $this->assertNotSame($questionId, $next['question_id'] ?? null, "Turn {$turn} mengembalikan pertanyaan yang sama.");

            $questionId = $next['question_id'];
        }
    }

    public function test_promotion_evaluated_after_four_correct_turns(): void
    {
        $user = $this->freeUser();
        $user->update(['remaining_trial_sessions' => 5]);
        Sanctum::actingAs($user);

        $sessionId = $this->postJson('api/v1/sessions/start', ['mode' => 'ADAPTIVE'])
            ->json('data.session_id');

        $questions = QuestionBank::where('cefr_level', 'A1')->get();

        $turnFourResponse = null;
        foreach ([1, 2, 3, 4] as $turn) {
            $turnFourResponse = $this->postJson('api/v1/sessions/evaluate-turn', [
                'session_id' => $sessionId,
                'turn_number' => $turn,
                'question_id' => $questions[$turn - 1]->id,
                'user_transcript' => self::GOOD_TRANSCRIPT,
            ]);
            $turnFourResponse->assertOk();
        }

        // 4 turn benar = 4 x 2 = 8 >= 6, tapi promosi TIDAK dievaluasi saat sesi berjalan.
        $turnFourResponse
            ->assertJsonPath('data.step_state', 'NORMAL')
            ->assertJsonPath('data.promotion', null);

        $user->refresh();
        $this->assertSame('A1', $user->current_cefr_level->value);

        // Promosi baru terjadi saat sesi selesai.
        $complete = $this->postJson('api/v1/sessions/complete', ['session_id' => $sessionId])
            ->assertOk()
            ->assertJsonPath('data.is_promoted', true)
            ->assertJsonPath('data.accumulated_score', 8)
            ->assertJsonPath('data.previous_cefr_level', 'A1')
            ->assertJsonPath('data.new_cefr_level', 'A2');

        $user->refresh();
        $this->assertSame('A2', $user->current_cefr_level->value);
    }

    public function test_promotion_uses_best_consecutive_four_turns(): void
    {
        $user = $this->freeUser();
        $user->update(['remaining_trial_sessions' => 5]);
        Sanctum::actingAs($user);

        $sessionId = $this->postJson('api/v1/sessions/start', ['mode' => 'ADAPTIVE'])
            ->json('data.session_id');

        $questions = QuestionBank::where('cefr_level', 'A1')->get();

        // Turn 1-2 error (0, 0), turn 3-5 benar (2, 2, 2).
        // Window 2-5 = 0+2+2+2 = 6 >= 6 -> promosi (bukan window pertama 1-4 = 0+0+2+2 = 4).
        foreach ([1, 2] as $turn) {
            $this->postJson('api/v1/sessions/evaluate-turn', [
                'session_id' => $sessionId,
                'turn_number' => $turn,
                'question_id' => $questions[$turn - 1]->id,
                'user_transcript' => 'Yesterday I go to the market.',
            ])->assertOk()->assertJsonPath('data.has_error', true);
        }
        foreach ([3, 4, 5] as $turn) {
            $this->postJson('api/v1/sessions/evaluate-turn', [
                'session_id' => $sessionId,
                'turn_number' => $turn,
                'question_id' => $questions[$turn - 1]->id,
                'user_transcript' => self::GOOD_TRANSCRIPT,
            ])->assertOk()->assertJsonPath('data.has_error', false);
        }

        // 4 turn berurutan terbaik (2-5 = 6) >= 6 -> promosi di akhir sesi.
        $this->postJson('api/v1/sessions/complete', ['session_id' => $sessionId])
            ->assertOk()
            ->assertJsonPath('data.accumulated_score', 6)
            ->assertJsonPath('data.is_promoted', true)
            ->assertJsonPath('data.new_cefr_level', 'A2');

        $user->refresh();
        $this->assertSame('A2', $user->current_cefr_level->value);
    }

    public function test_error_turn_does_not_create_pending_rule(): void
    {
        $user = $this->freeUser();
        Sanctum::actingAs($user);

        $sessionId = $this->postJson('api/v1/sessions/start', ['mode' => 'ADAPTIVE'])
            ->json('data.session_id');

        $questionId = QuestionBank::where('cefr_level', 'A1')->first()->id;

        $transcript = 'I go to Bandung last year.';

        $this->postJson('api/v1/sessions/evaluate-turn', [
            'session_id' => $sessionId,
            'turn_number' => 1,
            'question_id' => $questionId,
            'user_transcript' => $transcript,
        ])->assertOk()
            ->assertJsonPath('data.has_error', true)
            ->assertJsonPath('data.step_state', 'WAITING_REPETITION')
            ->assertJsonPath('data.correction_data.correct_way', 'I went to Bandung last year.');

        // Penilaian grammar langsung oleh LLM -> tidak ada pending rule yang dibuat.
        $this->assertDatabaseMissing('pending_grammar_rules', [
            'raw_user_input' => $transcript,
        ]);

        // Idempotent: evaluate lagi pada turn yang sama ditolak (sedang WAITING_REPETITION).
        $this->postJson('api/v1/sessions/evaluate-turn', [
            'session_id' => $sessionId,
            'turn_number' => 1,
            'question_id' => $questionId,
            'user_transcript' => $transcript,
        ])->assertStatus(422);
    }

    public function test_complete_session_returns_diagnostic_report(): void
    {
        $user = $this->freeUser();
        Sanctum::actingAs($user);

        $sessionId = $this->postJson('api/v1/sessions/start', ['mode' => 'ADAPTIVE'])
            ->json('data.session_id');

        $questionId = QuestionBank::where('cefr_level', 'A1')->first()->id;

        $this->postJson('api/v1/sessions/evaluate-turn', [
            'session_id' => $sessionId,
            'turn_number' => 1,
            'question_id' => $questionId,
            'user_transcript' => self::GOOD_TRANSCRIPT,
        ])->assertOk();

        $this->postJson('api/v1/sessions/complete', ['session_id' => $sessionId])
            ->assertOk()
            ->assertJsonPath('data.total_turns_completed', 1)
            ->assertJsonPath('data.remaining_trial_sessions', 0)
            ->assertJsonStructure([
                'data' => [
                    'session_id',
                    'cefr_level_current',
                    'accumulated_score',
                    'is_promoted',
                    'previous_cefr_level',
                    'new_cefr_level',
                    'remaining_trial_sessions',
                    'diagnostic_report' => [
                        'grammar_accuracy',
                        'frequent_errors',
                        'tutor_notes',
                    ],
                ],
            ]);
    }

    public function test_history_returns_completed_sessions_with_aggregates(): void
    {
        $user = $this->freeUser();
        Sanctum::actingAs($user);

        $sessionId = $this->postJson('api/v1/sessions/start', ['mode' => 'ADAPTIVE'])
            ->json('data.session_id');

        $questionId = QuestionBank::where('cefr_level', 'A1')->first()->id;

        $this->postJson('api/v1/sessions/evaluate-turn', [
            'session_id' => $sessionId,
            'turn_number' => 1,
            'question_id' => $questionId,
            'user_transcript' => self::GOOD_TRANSCRIPT,
        ])->assertOk();

        $this->postJson('api/v1/sessions/complete', ['session_id' => $sessionId])->assertOk();

        $this->getJson('api/v1/sessions/history')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.items.0.id', $sessionId)
            ->assertJsonPath('data.items.0.status', 'COMPLETED')
            ->assertJsonPath('data.items.0.turns', 1)
            ->assertJsonStructure([
                'data' => [
                    'items' => [[
                        'id', 'mode', 'start_level', 'current_level',
                        'total_score', 'score_pct', 'avg_grammar_accuracy',
                    ]],
                    'pagination' => ['current_page', 'last_page', 'per_page', 'total'],
                ],
            ]);
    }

    public function test_show_returns_session_detail_with_turns(): void
    {
        $user = $this->freeUser();
        Sanctum::actingAs($user);

        $sessionId = $this->postJson('api/v1/sessions/start', ['mode' => 'ADAPTIVE'])
            ->json('data.session_id');

        $questionId = QuestionBank::where('cefr_level', 'A1')->first()->id;

        $this->postJson('api/v1/sessions/evaluate-turn', [
            'session_id' => $sessionId,
            'turn_number' => 1,
            'question_id' => $questionId,
            'user_transcript' => self::GOOD_TRANSCRIPT,
        ])->assertOk();

        $this->getJson("api/v1/sessions/{$sessionId}")
            ->assertOk()
            ->assertJsonPath('data.session.id', $sessionId)
            ->assertJsonPath('data.turns.0.turn_number', 1)
            ->assertJsonPath('data.turns.0.has_error', false)
            ->assertJsonStructure([
                'data' => [
                    'session' => ['id', 'mode', 'status'],
                    'turns' => [[
                        'turn_number', 'user_said_text', 'correct_way_text',
                        'score_word_count', 'score_grammar',
                        'total_turn_score', 'has_error', 'step_state', 'repetition_success',
                    ]],
                ],
            ]);
    }

    public function test_show_returns_404_for_other_users_session(): void
    {
        $user = $this->freeUser();
        $other = User::factory()->create();
        $other->remaining_trial_sessions = 5;
        $other->save();
        Sanctum::actingAs($other);

        $questionId = QuestionBank::where('cefr_level', 'A1')->first()->id;

        $otherSession = $this->withToken(
            $other->createToken('t')->plainTextToken,
        )->postJson('api/v1/sessions/start', ['mode' => 'ADAPTIVE'])
            ->json('data.session_id');

        Sanctum::actingAs(User::find($user->id));

        $this->getJson("api/v1/sessions/{$otherSession}")
            ->assertNotFound()
            ->assertJsonPath('message', 'Sesi tidak ditemukan.');
    }
}