<?php

namespace App\Services;

use App\Repositories\PendingGrammarRuleRepository;

/**
 * Self-Learning Grammar (Sistem Pakar Adaptif):
 * Kalimat user yang TIDAK tertangkap rule aktif pada grammar_rules
 * dianggap salah dan otomatis ditangkap ke pending_grammar_rules
 * untuk direview Admin.
 *
 * Catatan: CorrectiveTextService hanya untuk menghasilkan correct_way_text
 * (nanti via LLM), TIDAK digunakan untuk penilaian. Penilaian grammar murni
 * bersandar pada grammar_rules (AdaptiveLevelingEngine).
 */
class SelfLearningGrammarService
{
    public function __construct(private readonly PendingGrammarRuleRepository $pending)
    {
    }

    /**
     * @param string $transcript   Transkrip asli user.
     * @param bool   $ruleMatched  True bila ada rule aktif yang match.
     * @param string $correctedText  Hasil perbaikan CorrectiveTextService (opsional).
     */
    public function captureNovelErrors(string $transcript, bool $ruleMatched, string $correctedText): void
    {
        if ($ruleMatched || $transcript === '') {
            return;
        }

        $suggestedRegex = $this->generateSuggestedRegex($transcript, $correctedText);

        $this->pending->captureCandidate(
            rawUserInput: $transcript,
            detectedError: $this->describeDiff($transcript, $correctedText),
            suggestedRegex: $suggestedRegex,
        );
    }

    private function generateSuggestedRegex(string $from, string $to): ?string
    {
        $fromWords = preg_split('/\s+/', mb_strtolower(trim($from))) ?: [];
        $toWords = preg_split('/\s+/', mb_strtolower(trim($to))) ?: [];

        $prefixLen = 0;
        while ($prefixLen < min(count($fromWords), count($toWords))
            && $fromWords[$prefixLen] === $toWords[$prefixLen]) {
            $prefixLen++;
        }

        $fromSuffix = array_values(array_reverse(array_slice($fromWords, $prefixLen)));
        $toSuffix = array_values(array_reverse(array_slice($toWords, $prefixLen)));

        $suffixLen = 0;
        while ($suffixLen < min(count($fromSuffix), count($toSuffix))
            && $fromSuffix[$suffixLen] === $toSuffix[$suffixLen]) {
            $suffixLen++;
        }

        $changedWords = array_slice(
            $fromWords,
            $prefixLen,
            count($fromWords) - $prefixLen - $suffixLen,
        );

        if (count($changedWords) === 0) {
            return null;
        }

        $escaped = array_map(fn (string $word) => preg_quote($word, '/'), $changedWords);

        return '/\b'.implode('\s+', $escaped).'\b/i';
    }

    private function describeDiff(string $from, string $to): string
    {
        if ($to === $from || $to === '') {
            return 'Kalimat tidak tertangkap rule grammar aktif mana pun; belum ada pola untuk divalidasi. Harap diverifikasi Admin.';
        }

        $regex = $this->generateSuggestedRegex($from, $to) ?? 'unknown pattern';

        return "Pola kesalahan baru terdeteksi dari koreksi otomatis (saran regex: {$regex}). "
            .'Belum ada rule aktif yang menangkap pola ini; harap diverifikasi Admin.';
    }
}