<?php

namespace App\Repositories;

use App\Models\ConversationSession;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SessionRepository extends Repository
{
    protected function model(): string
    {
        return ConversationSession::class;
    }

    public function create(array $attributes): ConversationSession
    {
        return $this->query()->create($attributes);
    }

    public function activeForUser(int $userId, string $sessionId): ?ConversationSession
    {
        return $this->query()
            ->where('user_id', $userId)
            ->where('id', $sessionId)
            ->where('status', 'ACTIVE')
            ->first();
    }

    public function findForUser(int $userId, string $sessionId): ?ConversationSession
    {
        return $this->query()
            ->where('user_id', $userId)
            ->where('id', $sessionId)
            ->with('topic:id,title')
            ->first();
    }

    public function forUserPaginated(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->query()
            ->where('user_id', $userId)
            ->with('topic:id,title')
            ->withCount('logs as logs_count')
            ->withSum('logs as total_score', 'total_turn_score')
            ->withSum('logs as grammar_score_sum', 'score_grammar')
            ->withSum('logs as curriculum_score_sum', 'curriculum_score')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function complete(ConversationSession $session): void
    {
        $session->update(['status' => 'COMPLETED', 'completed_at' => now()]);
    }

    public function completedSessionsCount(int $userId): int
    {
        return $this->query()
            ->where('user_id', $userId)
            ->where('status', 'COMPLETED')
            ->count();
    }

    public function completedCountInRange(int $userId, string $mode, Carbon $from, Carbon $to): int
    {
        return $this->query()
            ->where('user_id', $userId)
            ->where('mode', $mode)
            ->where('status', 'COMPLETED')
            ->whereBetween('completed_at', [$from, $to])
            ->count();
    }

    /**
     * Seluruh timestamp sesi selesai milik user, urut menurun (untuk streak harian).
     */
    public function completedAtDates(int $userId): Collection
    {
        return $this->query()
            ->where('user_id', $userId)
            ->where('status', 'COMPLETED')
            ->whereNotNull('completed_at')
            ->orderByDesc('completed_at')
            ->pluck('completed_at')
            ->map(fn ($when) => Carbon::parse($when))
            ->values();
    }

    public function distinctCompletedDates(int $userId): Collection
    {
        return $this->query()
            ->where('user_id', $userId)
            ->where('status', 'COMPLETED')
            ->whereNotNull('completed_at')
            ->orderByDesc('completed_at')
            ->pluck('completed_at')
            ->map(fn ($when) => (string) \Carbon\Carbon::parse($when)->toDateString())
            ->unique()
            ->values();
    }

    public function turnAggregates(int $userId): array
    {
        return (array) $this->query()
            ->where('user_id', $userId)
            ->where('status', 'COMPLETED')
            ->withCount('logs as turns')
            ->withSum('logs as total_score', 'total_turn_score')
            ->withSum('logs as grammar_sum', 'score_grammar')
            ->get()
            ->reduce(fn (array $carry, $session) => [
                'turns' => $carry['turns'] + (int) $session->turns,
                'total_score' => $carry['total_score'] + (int) $session->total_score,
                'grammar_sum' => $carry['grammar_sum'] + (int) $session->grammar_sum,
            ], ['turns' => 0, 'total_score' => 0, 'grammar_sum' => 0]);
    }

    public function distinctLearnedPhrases(int $userId): Collection
    {
        return \App\Models\ConversationLog::query()
            ->where('user_id', $userId)
            ->where('has_error', true)
            ->whereNotNull('correct_way_text')
            ->where('correct_way_text', '!=', '')
            ->distinct()
            ->pluck('correct_way_text')
            ->values();
    }

    public function setCurrentLevel(ConversationSession $session, string $level): void
    {
        $session->update(['current_level' => $level]);
    }
}