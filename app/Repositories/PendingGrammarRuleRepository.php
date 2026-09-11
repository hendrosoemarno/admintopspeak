<?php

namespace App\Repositories;

use App\Enums\PendingRuleStatus;
use App\Models\PendingGrammarRule;

class PendingGrammarRuleRepository extends Repository
{
    protected function model(): string
    {
        return PendingGrammarRule::class;
    }

    /**
     * Menangkap kandidat rule baru dari kesalahan user.
     * Tidak membuat duplikat untuk raw_user_input yang sama (idempotent).
     */
    public function captureCandidate(string $rawUserInput, string $detectedError, ?string $suggestedRegex): PendingGrammarRule
    {
        return $this->query()->firstOrCreate(
            ['raw_user_input' => $rawUserInput],
            [
                'detected_error' => $detectedError,
                'suggested_regex' => $suggestedRegex,
                'status' => PendingRuleStatus::PENDING,
            ],
        );
    }

    public function pendingCandidates(): \Illuminate\Support\Collection
    {
        return $this->query()
            ->where('status', PendingRuleStatus::PENDING)
            ->latest()
            ->get();
    }
}