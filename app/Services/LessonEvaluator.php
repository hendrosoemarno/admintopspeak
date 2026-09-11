<?php

namespace App\Services;

use App\Exceptions\LessonEvaluationUnavailableException;
use App\Models\Question;
use App\Services\Llm\LlmClient;

/**
 * Evaluator jawaban untuk kurikulum IELTS (Units/Lessons/Questions).
 * Grading berbobot: Key Point Checklist 50%, Grammatical Accuracy 25%,
 * Lexical Resource 25%. Evaluasi berjalan paralel via chatJsonMany.
 */
class LessonEvaluator
{
    /** Berat masing-masing dimensi (jumlah = 1.0). */
    private const WEIGHTS = [
        'key_point' => 0.50,
        'grammar' => 0.25,
        'lexical' => 0.25,
    ];

    /** Ambang kelulusan per soal (dari skor gabungan 0-100). */
    private const PASS_SCORE = 80;

    public function __construct(private readonly LlmClient $client)
    {
    }

    /**
     * Evaluasi sekumpulan jawaban untuk satu lesson.
     *
     * @param  Question  $question  Soal (model_answer & key_point sebagai referensi).
     * @param  array<string>  $transcripts  List transkrip jawaban user.
     * @return array<int, array>  Evaluasi per transkrip.
     */
    public function evaluateMany(Question $question, array $transcripts): array
    {
        if (! $this->client->enabled()) {
            throw LessonEvaluationUnavailableException::forUnavailable();
        }

        $calls = array_map(
            fn (string $transcript) => [$this->buildPrompt($question, $transcript), $transcript],
            $transcripts,
        );

        $rawResults = $this->client->chatJsonMany($calls);

        return array_map(
            fn (?array $raw) => $this->normalize($raw, $question->key_point),
            $rawResults,
        );
    }

    private function normalize(?array $raw, string $keyPoint): array
    {
        if (! is_array($raw)) {
            return [
                'is_correct' => false,
                'score' => 0,
                'key_point_detected' => false,
                'key_point_target' => $keyPoint,
                'grammar_feedback' => 'Evaluasi tidak dapat diproses.',
                'vocabulary_feedback' => '',
                'suggested_answer' => '',
            ];
        }

        $keyPointScore = $this->clamp((float) data_get($raw, 'key_point_score', 0.0));
        $grammarScore = $this->clamp((float) data_get($raw, 'grammar_score', 0.0));
        $lexicalScore = $this->clamp((float) data_get($raw, 'lexical_score', 0.0));

        $score = round(
            ($keyPointScore * self::WEIGHTS['key_point']
                + $grammarScore * self::WEIGHTS['grammar']
                + $lexicalScore * self::WEIGHTS['lexical']) * 100,
        );

        $correct = (bool) data_get($raw, 'key_point_detected', false) && $score >= self::PASS_SCORE;

        return [
            'is_correct' => $correct,
            'score' => (int) $score,
            'key_point_detected' => (bool) data_get($raw, 'key_point_detected', false),
            'key_point_target' => $keyPoint,
            'grammar_feedback' => (string) data_get($raw, 'grammar_feedback', ''),
            'vocabulary_feedback' => (string) data_get($raw, 'vocabulary_feedback', ''),
            'suggested_answer' => $correct ? '' : (string) data_get($raw, 'suggested_answer', ''),
        ];
    }

    private function clamp(float $value): float
    {
        return max(0.0, min(1.0, $value));
    }

    private function buildPrompt(Question $question, string $transcript): string
    {
        return str_replace(
            ['{question_text}', '{model_answer}', '{key_point}', '{user_transcript}'],
            [$question->question_text, $question->model_answer, $question->key_point, $transcript],
            (string) file_get_contents(resource_path('prompts/ielts_lesson_evaluator.txt')),
        );
    }
}