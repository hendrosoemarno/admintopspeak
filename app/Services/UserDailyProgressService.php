<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\SessionRepository;
use App\Repositories\UserLessonProgressRepository;
use Illuminate\Support\Carbon;

/**
 * Payload progres harian (badge latihan) untuk API v1.
 *
 * "Hari" dibulatkan berdasarkan zona waktu Asia/Jakarta, bukan zona server (UTC).
 * Sebuah hari dianggap "penuh" bila user menuntaskan minimal 1 sesi percakapan
 * (status COMPLETED) ATAU lulus minimal 1 lesson (status PASSED).
 */
class UserDailyProgressService
{
    private const TIMEZONE = 'Asia/Jakarta';

    public function __construct(
        private readonly SessionRepository $sessions,
        private readonly UserLessonProgressRepository $lessons,
    ) {
    }

    public function progress(User $user): array
    {
        $today = Carbon::now(self::TIMEZONE);
        $dayStartUtc = Carbon::parse($today->toDateString(), self::TIMEZONE)->setTimezone('UTC');
        $nextDayStartUtc = $dayStartUtc->copy()->addDay();

        $adaptiveToday = $this->sessions->completedCountInRange($user->id, 'ADAPTIVE', $dayStartUtc, $nextDayStartUtc);
        $thematicToday = $this->sessions->completedCountInRange($user->id, 'THEMATIC', $dayStartUtc, $nextDayStartUtc);
        $examLessonsToday = $this->lessons->passedCountInRange($user->id, $dayStartUtc, $nextDayStartUtc);

        $completedDates = $this->completedDateSet($user);

        return [
            'date' => $today->toDateString(),
            'adaptive_sessions' => $adaptiveToday,
            'thematic_sessions' => $thematicToday,
            'exam_lessons' => $examLessonsToday,
            'streak_days' => $this->computeCurrentStreak($completedDates, $today),
            'last_7_days' => $this->lastSevenDays($completedDates, $today),
        ];
    }

    /**
     * Tanggal 'Y-m-d' (Asia/Jakarta) yang memiliki minimal 1 aktivitas penuntasan.
     *
     * @return array<string, true>
     */
    private function completedDateSet(User $user): array
    {
        $dates = $this->sessions->completedAtDates($user->id)
            ->map(fn (Carbon $when) => $when->setTimezone(self::TIMEZONE)->toDateString())
            ->concat(
                $this->lessons->passedAtDates($user->id)
                    ->map(fn (Carbon $when) => $when->setTimezone(self::TIMEZONE)->toDateString()),
            )
            ->unique()
            ->values();

        return array_fill_keys($dates->all(), true);
    }

    /**
     * Jumlah hari beruntun sampai hari ini (atau kemarin bila hari ini belum ada
     * aktivitas), masing-masing dengan minimal 1 aktivitas penuntasan.
     *
     * @param  array<string, true>  $completedDates
     */
    private function computeCurrentStreak(array $completedDates, Carbon $today): int
    {
        $cursor = $today->toDateString();
        if (! isset($completedDates[$cursor])) {
            $cursor = $today->copy()->subDay()->toDateString();
        }

        $streak = 0;
        while (isset($completedDates[$cursor])) {
            $streak++;
            $cursor = Carbon::parse($cursor, self::TIMEZONE)->subDay()->toDateString();
        }

        return $streak;
    }

    /**
     * 7 hari terakhir termasuk hari ini, hari terlama di indeks 0.
     *
     * @param  array<string, true>  $completedDates
     * @return array<int, array{date: string, completed: bool}>
     */
    private function lastSevenDays(array $completedDates, Carbon $today): array
    {
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i)->toDateString();
            $days[] = [
                'date' => $date,
                'completed' => isset($completedDates[$date]),
            ];
        }

        return $days;
    }
}