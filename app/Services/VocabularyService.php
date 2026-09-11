<?php

namespace App\Services;

use App\Models\ConversationLog;
use App\Models\User;
use App\Repositories\VocabularyBankRepository;
use Illuminate\Support\Collection;

/**
 * Kosakata & frasa untuk fitur review belajar.
 */
class VocabularyService
{
    public function __construct(private readonly VocabularyBankRepository $bank)
    {
    }

    public function bank(array $filters = [], int $perPage = 20): array
    {
        $paginator = $this->bank->listPaginated($filters, $perPage);

        return [
            'items' => $paginator->map(fn ($item) => [
                'id' => $item->id,
                'word' => $item->word,
                'part_of_speech' => $item->part_of_speech,
                'cefr_level' => $item->cefr_level,
                'topic_category' => $item->topic_category,
            ])->all(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    /**
     * Frasa yang pernah dikoreksi ke user beserta status penguasaannya.
     */
    public function learned(User $user): Collection
    {
        $logs = ConversationLog::query()
            ->where('user_id', $user->id)
            ->where('has_error', true)
            ->whereNotNull('correct_way_text')
            ->where('correct_way_text', '!=', '')
            ->orderBy('created_at')
            ->get(['correct_way_text', 'user_said_text', 'repetition_success', 'created_at']);

        $saidRight = $logs
            ->map(fn ($log) => mb_strtolower(trim((string) $log->user_said_text)))
            ->filter()
            ->unique()
            ->values();

        return $logs
            ->groupBy(fn ($log) => mb_strtolower(trim($log->correct_way_text)))
            ->map(function ($group, $key) use ($saidRight) {
                $first = $group->first();

                return [
                    'phrase' => trim($first->correct_way_text),
                    'corrected_from' => $first->user_said_text,
                    'mastered' => $saidRight->contains($key)
                        || $group->contains(fn ($log) => (bool) $log->repetition_success),
                    'learned_at' => $group->last()->created_at?->toIso8601String(),
                ];
            })
            ->values();
    }
}