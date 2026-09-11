<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\SessionRepository;
use Illuminate\Support\Carbon;

/**
 * Agregat statistik & streak belajar untuk home screen klien.
 */
class UserStatsService
{
    public function __construct(private readonly SessionRepository $sessions)
    {
    }

    public function stats(User $user): array
    {
        $dates = $this->sessions->distinctCompletedDates($user->id)->all();

        [$current, $longest] = $this->computeStreaks($dates);

        $aggregates = $this->sessions->turnAggregates($user->id);
        $turns = $aggregates['turns'];
        $learnedPhrases = $this->sessions->distinctLearnedPhrases($user->id);

        return [
            'current_cefr_level' => $user->current_cefr_level->value,
            'is_premium' => $user->isPremiumActive(),
            'remaining_trial_sessions' => $user->remaining_trial_sessions,
            'total_sessions_completed' => $this->sessions->completedSessionsCount($user->id),
            'total_turns' => $turns,
            'avg_turn_score' => $turns > 0 ? round($aggregates['total_score'] / $turns, 1) : 0,
            'grammar_accuracy_pct' => $turns > 0
                ? (int) round($aggregates['grammar_sum'] / $turns * 100)
                : 0,
            'phrases_learned' => $learnedPhrases->count(),
            'current_streak_days' => $current,
            'longest_streak_days' => $longest,
        ];
    }

    /**
     * @param  string[]  $dates  Tanggal 'Y-m-d' sesi selesai, urut menurun.
     * @return array{0:int, 1:int}
     */
    private function computeStreaks(array $dates): array
    {
        $set = array_flip($dates);

        $cursor = Carbon::today()->toDateString();
        if (! isset($set[$cursor])) {
            $cursor = Carbon::today()->subDay()->toDateString();
        }

        $current = 0;
        while (isset($set[$cursor])) {
            $current++;
            $cursor = Carbon::parse($cursor)->subDay()->toDateString();
        }

        $longest = 0;
        $run = 0;
        $previous = null;
        foreach ($dates as $date) {
            if ($previous !== null && (int) abs(Carbon::parse($previous)->diffInDays(Carbon::parse($date))) === 1) {
                $run++;
            } else {
                $run = 1;
            }
            $longest = max($longest, $run);
            $previous = $date;
        }

        return [$current, $longest];
    }
}