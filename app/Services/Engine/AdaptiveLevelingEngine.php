<?php

namespace App\Services\Engine;

use App\Models\Question;
use App\Models\QuestionBank;
use App\Services\Llm\LlmClient;
use App\Services\Llm\LlmGrammarEvaluator;

/**
 * Mesin skor per-turn sesuai bisnis rule TopSpeak (0-2 poin / turn):
 * - Word Count (threshold kata per level CEFR)    -> 0/1
 * - Grammar Accuracy (dinilai langsung oleh LLM)   -> 0/1
 *
 * Scoring fluency dihapus total (LLM maupun penilaian percakapan).
 *
 * Penilaian grammar dilakukan oleh LLM: kalimat benar => 1, ada kesalahan => 0,
 * dan LLM juga menghasilkan versi koreksi. Bila LLM tidak tersedia, evaluasi
 * grammar diblokir (GrammarEvaluationUnavailableException -> HTTP 422).
 */
class AdaptiveLevelingEngine
{
    public const GRAMMAR_VIOLATION_TOLERANCE = 0;

    public const PROMOTION_THRESHOLD = 6;

    /** Target minimal jumlah kata jawaban standar per level CEFR. */
    public const WORD_COUNT_THRESHOLDS = [
        'A1' => 12,
        'A2' => 20,
        'B1' => 35,
        'B2' => 55,
        'C1' => 75,
        'C2' => 90,
    ];

    public function __construct(
        private readonly LlmClient $client,
        private readonly LlmGrammarEvaluator $grammarEvaluator,
    ) {
    }

    public function score(string $transcript, QuestionBank|Question $question, ?string $cefrLevel = null): array
    {
        // Kalimat fallback / hedging (mis. "i don't know", "something like that")
        // TIDAK dinilai: grammar = 0 tanpa memanggil LLM.
        if ($this->isFallbackResponse($transcript)) {
            return [
                'word_count_score' => 0,
                'grammar_score' => 0,
                'total_turn_score' => 0,
                'has_error' => true,
                'violations' => [[
                    'rule_code' => 'FALLBACK',
                    'category' => 'Hedging / Fallback',
                    'description' => 'Kalimat tidak dijawab (fallback/hedging). Jawab pertanyaan dengan kalimat utuh.',
                ]],
                'rule_matched' => true,
                'matches_positive' => false,
                'corrected_sentence' => null,
                'error_description' => 'Kalimat tidak dijawab (fallback/hedging).',
            ];
        }

        if (! $this->client->enabled()) {
            throw \App\Exceptions\GrammarEvaluationUnavailableException::forUnavailable();
        }

        $grammarEvaluation = $this->grammarEvaluator->evaluate($transcript, $question->question_text);

        // Soal kurikulum (App\Models\Question) tidak punya cefr_level, pakai
        // level sesi (curriculum: current_level) yang diteruskan sebagai override.
        $level = $cefrLevel ?? $question->cefr_level?->value ?? 'A1';
        $threshold = self::WORD_COUNT_THRESHOLDS[$level] ?? 12;
        $wordScore = str_word_count($transcript) >= $threshold ? 1 : 0;

        // Grammar dinilai langsung oleh LLM:
        //  - is_correct true  -> grammar benar (skor 1).
        //  - is_correct false -> ada kesalahan (skor 0).
        $grammarScore = $grammarEvaluation['is_correct'] ? 1 : 0;
        $hasError = ! $grammarEvaluation['is_correct'];

        return [
            'word_count_score' => $wordScore,
            'grammar_score' => $grammarScore,
            'total_turn_score' => $wordScore + $grammarScore,
            'has_error' => $hasError,
            'violations' => $hasError
                ? [[
                    'rule_code' => 'LLM',
                    'category' => 'Grammar Check',
                    'description' => $grammarEvaluation['error_description'] ?? 'Kalimat mengandung kesalahan grammar.',
                ]]
                : [],
            'rule_matched' => $hasError,
            'matches_positive' => ! $hasError,
            'corrected_sentence' => $grammarEvaluation['corrected_sentence'],
            'error_description' => $grammarEvaluation['error_description'],
        ];
    }

    public function isPromoted(array $scores): bool
    {
        return $this->bestConsecutiveScore($scores, 4) >= self::PROMOTION_THRESHOLD;
    }

    /**
     * Skor terbaik dari N turn berurutan di dalam deret skor.
     * Jendela digeser per turn (mis. N=4 -> turn 1-4, 2-5, dst).
     */
    private function bestConsecutiveScore(array $scores, int $window): int
    {
        $best = 0;
        $count = count($scores);

        for ($i = 0; $i + $window - 1 < $count; $i++) {
            $sum = 0;

            for ($j = 0; $j < $window; $j++) {
                $sum += $scores[$i + $j];
            }

            $best = max($best, $sum);
        }

        return (int) $best;
    }

    /**
     * Deteksi kalimat fallback / hedging yang tidak layak dinilai, misalnya:
     *   - "i don't know", "i do not know", "i don't understand"
     *   - "something like that", "i'm not sure", "i have no idea"
     *   - "i forgot", "i don't remember"
     * Bila terdeteksi, grammar otomatis 0 tanpa panggilan LLM.
     */
    private function isFallbackResponse(string $transcript): bool
    {
        $value = mb_strtolower(trim($transcript));

        if ($value === '') {
            return true;
        }

        $patterns = [
            '/\bi\s+don\s*\'?\s*t\s+know\b/i',
            '/\bi\s+do\s+not\s+know\b/i',
            '/\bi\s+don\s*\'?\s*t\s+understand\b/i',
            '/\bi\s+do\s+not\s+understand\b/i',
            '/\bi\s+have\s+no\s+idea\b/i',
            '/\bi\s+don\s*\'?\s*t\s+remember\b/i',
            '/\bi\s+do\s+not\s+remember\b/i',
            '/\bi\s+forgot\b/i',
            '/\bi\s+am\s+not\s+sure\b/i',
            '/\bi\s+am\s+not\s+certain\b/i',
            '/\bsomething\s+like\s+that\b/i',
            '/\bjust\s+(?:saying|guessing)\b/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value) === 1) {
                return true;
            }
        }

        return false;
    }
}