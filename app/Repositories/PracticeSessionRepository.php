<?php

namespace App\Repositories;

use App\Models\PracticeSession;
use Illuminate\Support\Collection;

class PracticeSessionRepository extends Repository
{
    protected function model(): string
    {
        return PracticeSession::class;
    }

    public function start(int $userId, int $lessonId, string $sessionId): PracticeSession
    {
        return $this->query()->create([
            'user_id' => $userId,
            'lesson_id' => $lessonId,
            'session_id' => $sessionId,
            'total_questions' => 0,
            'correct_count' => 0,
            'score' => 0,
            'is_passed' => false,
            'total_keypoints' => 0,
            'correct_keypoints' => 0,
        ]);
    }

    /**
     * Update metrik sesi saat complete() dipanggil. Bila record belum ada
     * (misal sesi lama sebelum fitur ini rilis), dibuat on-the-fly.
     */
    public function completeAttempt(int $userId, string $sessionId, array $metrics): PracticeSession
    {
        return $this->query()->updateOrCreate(
            ['user_id' => $userId, 'session_id' => $sessionId],
            $metrics,
        );
    }

    /**
     * N sesi lulus terakhir, diurutkan menurun dari yang terbaru.
     */
    public function lastPassedSessions(int $userId, int $limit): Collection
    {
        return $this->query()
            ->where('user_id', $userId)
            ->where('is_passed', true)
            ->whereNotNull('completed_at')
            ->orderByDesc('completed_at')
            ->limit($limit)
            ->get(['score']);
    }

    /**
     * Agregat key point pada sesi-sesi lulus.
     *
     * @return array{0:int, 1:int}  [total_keypoints, correct_keypoints]
     */
    public function passedSessionKeyPointTotals(int $userId): array
    {
        $row = $this->query()
            ->where('user_id', $userId)
            ->where('is_passed', true)
            ->selectRaw('COALESCE(SUM(total_keypoints), 0) AS total_keypoints, COALESCE(SUM(correct_keypoints), 0) AS correct_keypoints')
            ->first();

        return [(int) $row->total_keypoints, (int) $row->correct_keypoints];
    }
}