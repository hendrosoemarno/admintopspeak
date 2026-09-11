<?php

namespace App\Services\Engine;

/**
 * Verifikasi pengulangan (Say Again / Repetition Loop).
 * Kecocokan >= 80% dianggap berhasil.
 */
class RepetitionService
{
    public const SIMILARITY_THRESHOLD = 80;

    public function similarity(string $first, string $second): int
    {
        $first = mb_strtolower(trim($first));
        $second = mb_strtolower(trim($second));

        if ($first === $second) {
            return 100;
        }

        similar_text($first, $second, $percent);

        return (int) round($percent);
    }

    public function isSuccess(string $repetition, string $expected): bool
    {
        return $this->similarity($repetition, $expected) >= self::SIMILARITY_THRESHOLD;
    }

    public function transitionSpeech(bool $success, ?string $nextQuestion): string
    {
        $question = $nextQuestion ?? 'let us finish this session';

        if (! str_ends_with(trim($question), '?')) {
            $question .= '?';
        }

        $prefix = $success ? 'Alright, got it.' : 'Let\'s move on.';

        return "{$prefix} Now, {$question}";
    }
}