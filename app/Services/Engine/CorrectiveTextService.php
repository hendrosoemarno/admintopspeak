<?php

namespace App\Services\Engine;

use App\Services\Grammar\WordTransformationService;

/**
 * Layanan koreksi teks sederhana (heuristic) untuk menghasilkan `correct_way_text`.
 * Mengganti pola kesalahan umum sesuai rule grammar TopSpeak.
 *
 * Pipeline: Grammar Evaluator (deteksi violation) -> WordTransformationService
 * (lookup O(1) dari JSON) -> token terkoreksi -> payload akhir.
 */
class CorrectiveTextService
{
    public function __construct(
        private readonly WordTransformationService $transform,
    ) {
    }

    public function correct(string $text): string
    {
        $fixed = $text;

        $fixed = preg_replace('/\b(he|she|it)\s+go\b/i', '$1 goes', $fixed);
        $fixed = preg_replace('/\b(he|she|it)\s+have\b/i', '$1 has', $fixed);
        $fixed = preg_replace('/\b(he|she|it)\s+do\b/i', '$1 does', $fixed);
        $fixed = preg_replace('/\b(they|we|you)\s+goes\b/i', '$1 go', $fixed);
        $fixed = preg_replace('/\b(they|we|you)\s+has\b/i', '$1 have', $fixed);
        $fixed = preg_replace('/\bdepend\s+(at|to)\b/i', 'depend on', $fixed);
        $fixed = preg_replace('/\binterested\s+for\b/i', 'interested in', $fixed);

        // Tenses lampau bila ada penanda waktu lampau.
        // Kata kerja diubah via WordTransformationService (data-driven, O(1)).
        if (preg_match('/\b(yesterday|ago|last\s+\w+)\b/i', $fixed)) {
            $fixed = $this->transformPastSimple($fixed);
        }

        $fixed = preg_replace('/\ba\s+(apple|orange|hour|umbrella|honest)\b/i', 'an $1', $fixed);
        $fixed = preg_replace('/\ban\s+(book|car|dog|market)\b/i', 'a $1', $fixed);
        $fixed = preg_replace('/\b(can|must|should)\s+to\s+(\w+)/i', '$1 $2', $fixed);
        $fixed = preg_replace('/\benjoy\s+to\s+(\w+ing)\b/i', 'enjoy $1', $fixed);

        return $fixed !== $text ? $fixed : $text;
    }

    /**
     * Ubah kata kerja irregular yang dikenal menjadi past simple (V2).
     * Hanya kata kerja irregular yang disubstitusi agar noun/adjective
     * lain tidak ikut berubah bentuk.
     */
    private function transformPastSimple(string $text): string
    {
        return preg_replace_callback(
            '/\b([a-z]+)\b/i',
            fn (array $match) => $this->transform->knownPastSimple($match[1]) ?? $match[0],
            $text,
        );
    }
}