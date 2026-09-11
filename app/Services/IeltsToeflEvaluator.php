<?php

namespace App\Services;

use App\Exceptions\AssessmentEvaluationUnavailableException;
use App\Services\Llm\LlmClient;

/**
 * Evaluator IELTS Speaking (Band 1.0–9.0) & TOEFL iBT Speaking (Scale 0–4 / 0–30).
 *
 * Pure LLM evaluator: deterministik (temperature 0.0), structured JSON output,
 * dan safety guard binary threshold (final_fluency) di sisi backend.
 */
class IeltsToeflEvaluator
{
    private const BINARY_THRESHOLD = 0.50;

    public function __construct(private readonly LlmClient $client)
    {
    }

    /**
     * Evaluasi satu jawaban speaking.
     *
     * @param  array{test_type: string, task_type: string, prompt_question: string, user_transcript: string, duration_seconds?: ?int}  $data
     */
    public function evaluate(array $data): array
    {
        if (! $this->client->enabled()) {
            throw AssessmentEvaluationUnavailableException::forUnavailable();
        }

        $system = $data['test_type'] === 'IELTS'
            ? $this->buildIeltsPrompt($data)
            : $this->buildToeflPrompt($data);

        $raw = $this->client->chatJson($system, $data['user_transcript'], temperature: 0.0);

        if (! is_array($raw)) {
            throw AssessmentEvaluationUnavailableException::forUnavailable();
        }

        return $this->normalize($data['test_type'], $raw);
    }

    /**
     * Safety guard backend: paksa evaluasi binary threshold terhadap s_total,
     * agar LLM tidak bisa mengembalikan nilai final_fluency yang tidak valid.
     */
    private function normalize(string $testType, array $raw): array
    {
        $raw['test_type'] = $testType;

        $sTotal = (float) data_get($raw, 'fluency_matrix.s_total', 0.0);
        $raw['fluency_matrix']['s_total'] = round($sTotal, 2);
        $raw['fluency_matrix']['final_fluency'] = $sTotal >= self::BINARY_THRESHOLD ? 1 : 0;

        $raw['scores']['overall_score'] = $testType === 'IELTS'
            ? (float) data_get($raw, 'scores.overall_band', 0.0)
            : (float) data_get($raw, 'scores.scaled_score_30', 0.0);

        return $raw;
    }

    private function buildIeltsPrompt(array $data): string
    {
        return $this->fill(resource_path('prompts/ielts_evaluator.txt'), $data);
    }

    private function buildToeflPrompt(array $data): string
    {
        return $this->fill(resource_path('prompts/toefl_evaluator.txt'), $data);
    }

    private function fill(string $path, array $data): string
    {
        return str_replace(
            ['{prompt_question}', '{user_transcript}'],
            [$data['prompt_question'], $data['user_transcript']],
            (string) file_get_contents($path),
        );
    }
}
