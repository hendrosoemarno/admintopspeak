<?php

namespace Tests\Feature;

use App\Models\LlmSetting;
use App\Models\QuestionBank;
use App\Services\Engine\AdaptiveLevelingEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Per-turn scoring tanpa fluency: word_count (0/1) + grammar (0/1) = 0-2.
 */
class EngineTurnScoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['llm.api_key' => 'test-key', 'llm.base_url' => 'https://api.openai.com/v1']);
        LlmSetting::current()->update([
            'is_enabled' => true,
            'api_key' => 'test-key',
            'base_url' => 'https://api.openai.com/v1',
        ]);
    }

    private function question(): QuestionBank
    {
        return QuestionBank::create([
            'test_type' => 'ADAPTIVE',
            'part_number' => 1,
            'cefr_level' => 'A1',
            'question_text' => 'Test question',
            'required_vocab_tags' => ['name', 'city', 'family'],
            'is_starter' => false,
            'topic_category' => 'General',
            'metadata' => ['audio_url' => null],
        ]);
    }

    private function fakeGrammar(bool $isCorrect): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [[
                    'message' => ['content' => json_encode([
                        'is_correct' => $isCorrect,
                        'corrected_sentence' => $isCorrect ? '' : 'I usually wake up at five o clock in the morning.',
                        'error_description' => $isCorrect ? '' : 'Verb tense salah.',
                    ])],
                ]],
            ], 200),
        ]);
    }

    public function test_correct_turn_scores_word_count_plus_grammar(): void
    {
        $this->fakeGrammar(true);

        $engine = app(AdaptiveLevelingEngine::class);
        $scores = $engine->score('My name is Budi and I live in the city of Jakarta with my family.', $this->question());

        $this->assertSame(1, $scores['word_count_score']);
        $this->assertSame(1, $scores['grammar_score']);
        $this->assertSame(2, $scores['total_turn_score']);
        $this->assertFalse($scores['has_error']);
        $this->assertArrayNotHasKey('fluency_score', $scores);
        $this->assertArrayNotHasKey('fluency_detail', $scores);
    }

    public function test_grammar_error_gives_zero_turn_score_when_words_below_threshold(): void
    {
        $this->fakeGrammar(false);

        $engine = app(AdaptiveLevelingEngine::class);
        $scores = $engine->score('I go to the market yesterday', $this->question());

        // "I go to the market yesterday" = 5 kata < 12 (A1) -> word 0, grammar 0.
        $this->assertSame(0, $scores['word_count_score']);
        $this->assertSame(0, $scores['grammar_score']);
        $this->assertSame(0, $scores['total_turn_score']);
        $this->assertTrue($scores['has_error']);
    }

    public function test_fallback_response_scores_zero_without_llm_call(): void
    {
        $engine = app(AdaptiveLevelingEngine::class);
        $scores = $engine->score('I do not know', $this->question());

        $this->assertSame(0, $scores['word_count_score']);
        $this->assertSame(0, $scores['grammar_score']);
        $this->assertSame(0, $scores['total_turn_score']);
        $this->assertTrue($scores['has_error']);
        $this->assertSame('FALLBACK', $scores['violations'][0]['rule_code']);
    }

    public function test_hedging_phrase_scores_zero(): void
    {
        $engine = app(AdaptiveLevelingEngine::class);
        $scores = $engine->score('something like that', $this->question());

        $this->assertSame(0, $scores['grammar_score']);
        $this->assertSame(0, $scores['total_turn_score']);
    }

    public function test_empty_transcript_scores_zero(): void
    {
        $engine = app(AdaptiveLevelingEngine::class);
        $scores = $engine->score('   ', $this->question());

        $this->assertSame(0, $scores['total_turn_score']);
        $this->assertTrue($scores['has_error']);
    }

    public function test_promotion_requires_six_points_within_four_turns(): void
    {
        // 3 turn sempurna (2) + 1 turn rendah (0) dari 4 turn berurutan = 6 >= 6.
        $engine = app(AdaptiveLevelingEngine::class);

        $this->assertTrue($engine->isPromoted([2, 2, 2, 0]));
    }

    public function test_promotion_not_reached_with_five_points(): void
    {
        $engine = app(AdaptiveLevelingEngine::class);

        $this->assertFalse($engine->isPromoted([2, 2, 1, 0]));
    }

    public function test_promotion_uses_best_four_consecutive_window(): void
    {
        $engine = app(AdaptiveLevelingEngine::class);

        // Window 1-4 = 2+0+2+2 = 6 -> promosi (bukan rerata seluruh turn).
        $this->assertTrue($engine->isPromoted([2, 0, 2, 2, 0, 0]));
        // Tanpa ada jendela 4-turn mencapai 6 -> tidak promosi.
        $this->assertFalse($engine->isPromoted([2, 1, 1, 1, 2, 0]));
    }
}