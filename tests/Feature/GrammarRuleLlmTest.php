<?php

namespace Tests\Feature;

use App\Livewire\Admin\PendingRules\Index as PendingRulesIndex;
use App\Models\GrammarRule;
use App\Models\LlmSetting;
use App\Models\PendingGrammarRule;
use App\Models\QuestionBank;
use App\Models\User;
use App\Services\Engine\AdaptiveLevelingEngine;
use App\Services\Llm\GrammarRuleSuggestionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class GrammarRuleLlmTest extends TestCase
{
    use RefreshDatabase;

    private function enableLlm(string $apiKey = 'test-key'): void
    {
        config(['llm.api_key' => $apiKey, 'llm.base_url' => 'https://api.openai.com/v1']);

        LlmSetting::current()->update([
            'is_enabled' => true,
            'provider' => 'openai',
            'base_url' => 'https://api.openai.com/v1',
            'api_key' => $apiKey,
            'model' => 'gpt-4o-mini',
            'timeout' => 30,
        ]);
    }

    private function disableLlm(): void
    {
        config(['llm.api_key' => '']);

        LlmSetting::current()->update([
            'is_enabled' => false,
            'api_key' => null,
        ]);
    }

    public function test_approve_with_llm_creates_rule_from_llm_response(): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [[
                    'message' => ['content' => json_encode([
                        'correct_sentence' => 'I am agreeing with you',
                        'regex_pattern' => '/\\bi\\s+am\\s+agreeing\\b/i',
                        'category' => 'Subject-Verb Agreement',
                        'cefr_level' => 'A2',
                        'description' => 'Agree adalah verb, gunakan -ing setelah to be.',
                    ])],
                ]],
            ], 200),
        ]);

        $this->enableLlm();

        $pending = PendingGrammarRule::create([
            'raw_user_input' => 'I am agree with you',
            'detected_error' => 'Agree bukan adjective.',
            'suggested_regex' => '/\\bagree\\b/i',
            'status' => 'PENDING',
        ]);

        Livewire::test(PendingRulesIndex::class)
            ->call('approve', $pending->id)
            ->assertSet('previewPendingId', $pending->id)
            ->call('saveRule');

        $rule = $pending->fresh()->grammarRule;
        $this->assertNotNull($rule);
        $this->assertSame('USER_001', $rule->rule_code);
        $this->assertSame('positive', $rule->rule_type);
        $this->assertSame('llm', $rule->source);
        $this->assertSame('/\bi\s+am\s+agreeing\b/i', $rule->regex_pattern);
        $this->assertSame('A2', $rule->cefr_level->value);
        $this->assertNotNull($rule->llm_meta);

        $this->assertSame('I am agreeing with you', $pending->fresh()->suggested_correct_sentence);
    }

    public function test_approve_without_llm_configured_falls_back_to_heuristic(): void
    {
        $this->disableLlm();

        $pending = PendingGrammarRule::create([
            'raw_user_input' => 'I am agree with you',
            'detected_error' => 'Agree bukan adjective.',
            'suggested_regex' => '/\\bagree\\b/i',
            'status' => 'PENDING',
        ]);

        Livewire::test(PendingRulesIndex::class)
            ->call('approve', $pending->id)
            ->assertSet('previewPendingId', $pending->id)
            ->call('saveRule');

        $rule = $pending->fresh()->grammarRule;
        $this->assertNotNull($rule);
        $this->assertSame('error', $rule->rule_type);
        $this->assertSame('user', $rule->source);
        $this->assertSame('/\bagree\b/i', $rule->regex_pattern);
    }

    public function test_approve_again_updates_existing_rule(): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [[
                    'message' => ['content' => json_encode([
                        'correct_sentence' => 'I am agreeing with you',
                        'regex_pattern' => '/\\bi\\s+am\\s+agreeing\\b/i',
                        'category' => 'User Suggested',
                        'cefr_level' => 'A1',
                        'description' => 'Pola benar.',
                    ])],
                ]],
            ], 200),
        ]);

        $this->enableLlm();

        $rule = GrammarRule::create([
            'rule_code' => 'USER_001',
            'category' => 'User Suggested',
            'rule_type' => 'error',
            'source' => 'user',
            'cefr_level' => 'A1',
            'regex_pattern' => '/\\bagree\\b/i',
            'description' => 'Lama.',
            'is_active' => true,
        ]);

        $pending = PendingGrammarRule::create([
            'raw_user_input' => 'I am agreeing with you',
            'detected_error' => 'Ok.',
            'suggested_regex' => '/\\bagree\\b/i',
            'grammar_rule_id' => $rule->id,
            'status' => 'PENDING',
        ]);

        Livewire::test(PendingRulesIndex::class)
            ->call('approve', $pending->id)
            ->assertSet('previewPendingId', $pending->id)
            ->call('saveRule');

        $this->assertSame(1, GrammarRule::count());
        $rule->refresh();
        $this->assertSame('positive', $rule->rule_type);
        $this->assertSame('llm', $rule->source);
        $this->assertSame('/\bi\s+am\s+agreeing\b/i', $rule->regex_pattern);
    }

    public function test_suggestion_service_falls_back_when_llm_returns_invalid_json(): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [['message' => ['content' => 'not json at all']]],
            ], 200),
        ]);

        $this->enableLlm();

        $service = app(GrammarRuleSuggestionService::class);
        $result = $service->suggest('I am agree with you', 'Agree bukan adjective.', '/\\bagree\\b/i');

        $this->assertSame('error', $result['rule_type']);
        $this->assertSame('user', $result['source']);
        $this->assertSame('/\bagree\b/i', $result['regex_pattern']);
    }

    public function test_llm_grammar_evaluator_scores_correct_sentence_one(): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [[
                    'message' => ['content' => json_encode([
                        'is_correct' => true,
                        'corrected_sentence' => '',
                        'error_description' => '',
                    ])],
                ]],
            ], 200),
        ]);

        $this->enableLlm();

        $engine = app(AdaptiveLevelingEngine::class);
        $scores = $engine->score('I usually wake up at five o clock in the morning', $this->question());

        $this->assertSame([], $scores['violations']);
        $this->assertTrue($scores['matches_positive']);
        $this->assertSame(1, $scores['grammar_score']);
        $this->assertFalse($scores['has_error']);
        $this->assertNull($scores['corrected_sentence']);
    }

    public function test_llm_grammar_evaluator_scores_incorrect_sentence_zero(): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [[
                    'message' => ['content' => json_encode([
                        'is_correct' => false,
                        'corrected_sentence' => 'I usually wake up at five o clock in the morning.',
                        'error_description' => 'Verb tense salah.',
                    ])],
                ]],
            ], 200),
        ]);

        $this->enableLlm();

        $engine = app(AdaptiveLevelingEngine::class);
        $scores = $engine->score('I usually wake up at five o clock in the morning', $this->question());

        $this->assertNotEmpty($scores['violations']);
        $this->assertFalse($scores['matches_positive']);
        $this->assertSame(0, $scores['grammar_score']);
        $this->assertTrue($scores['has_error']);
        $this->assertSame('I usually wake up at five o clock in the morning.', $scores['corrected_sentence']);
    }

    public function test_grammar_evaluation_throws_422_when_llm_unavailable(): void
    {
        $this->disableLlm();

        $engine = app(AdaptiveLevelingEngine::class);

        $this->expectException(\App\Exceptions\GrammarEvaluationUnavailableException::class);

        $engine->score('I usually wake up at five o clock in the morning', $this->question());
    }

    public function test_llm_grammar_evaluator_flags_incomplete_fragment_as_error(): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [[
                    'message' => ['content' => json_encode([
                        'is_correct' => false,
                        'corrected_sentence' => 'My mind is full of ideas about my hobby.',
                        'error_description' => 'Kalimat tidak lengkap (fragment) dan tidak menjawab pertanyaan.',
                    ])],
                ]],
            ], 200),
        ]);

        $this->enableLlm();

        $engine = app(AdaptiveLevelingEngine::class);
        $scores = $engine->score('my mind will be', $this->question());

        $this->assertSame(0, $scores['grammar_score']);
        $this->assertTrue($scores['has_error']);
        $this->assertNotEmpty($scores['violations']);
        $this->assertNotNull($scores['corrected_sentence']);
        $this->assertSame(0, $scores['total_turn_score']);
    }

    public function test_llm_grammar_evaluator_passes_question_context_to_prompt(): void
    {
        Http::fake([
            '*' => function ($request) {
                $system = '';
                $userContent = '';
                foreach ($request->data()['messages'] ?? [] as $m) {
                    if (($m['role'] ?? '') === 'system') {
                        $system = $m['content'] ?? '';
                    }
                    if (($m['role'] ?? '') === 'user') {
                        $userContent = $m['content'] ?? '';
                    }
                }

                $this->assertStringContainsString('complete, coherent', $system);
                $this->assertStringContainsString('incomplete/incoherent fragment', $system);
                $this->assertStringContainsString('Daily routine question please.', $userContent);

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

        $this->enableLlm();

        $engine = app(AdaptiveLevelingEngine::class);
        $scores = $engine->score('I like waking up early.', $this->question());

        $this->assertSame(1, $scores['grammar_score']);
        $this->assertFalse($scores['has_error']);
    }

    private function question(): QuestionBank
    {
        return QuestionBank::create([
            'test_type' => 'ADAPTIVE',
            'part_number' => 1,
            'cefr_level' => 'A1',
            'question_text' => 'Daily routine question please.',
            'required_vocab_tags' => ['wake', 'morning'],
            'is_starter' => false,
            'topic_category' => 'Daily Routine',
            'metadata' => ['audio_url' => null],
        ]);
    }
}
