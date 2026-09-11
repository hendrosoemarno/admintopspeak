<?php

namespace App\Repositories;

use App\Enums\LessonProgressStatus;
use App\Models\UserLessonProgress;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class UserLessonProgressRepository extends Repository
{
    protected function model(): string
    {
        return UserLessonProgress::class;
    }

    public function updateOrCreateStatus(int $userId, int $lessonId, LessonProgressStatus $status): UserLessonProgress
    {
        return UserLessonProgress::updateOrCreate(
            ['user_id' => $userId, 'lesson_id' => $lessonId],
            ['status' => $status->value],
        );
    }

    /**
     * Progress per user untuk seluruh lesson (untuk payload curriculum).
     *
     * Menggunakan toBase() agar nilai status diambil mentah dari DB (string),
     * bukan di-cast menjadi enum (kebutuhan komparasi & payload JSON).
     *
     * @return array<int, string>  [lesson_id => status]
     */
    public function statusMapForUser(int $userId): array
    {
        return $this->query()
            ->toBase()
            ->where('user_id', $userId)
            ->pluck('status', 'lesson_id')
            ->all();
    }

    public function passedLessonCount(int $userId): int
    {
        return $this->query()
            ->where('user_id', $userId)
            ->where('status', LessonProgressStatus::PASSED->value)
            ->count();
    }

    public function passedCountInRange(int $userId, Carbon $from, Carbon $to): int
    {
        return $this->query()
            ->where('user_id', $userId)
            ->where('status', LessonProgressStatus::PASSED->value)
            ->whereBetween('created_at', [$from, $to])
            ->count();
    }

    /**
     * Seluruh timestamp lesson lulus milik user (untuk streak harian).
     */
    public function passedAtDates(int $userId): Collection
    {
        return $this->query()
            ->where('user_id', $userId)
            ->where('status', LessonProgressStatus::PASSED->value)
            ->pluck('created_at')
            ->map(fn ($when) => Carbon::parse($when))
            ->values();
    }
}