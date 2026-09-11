<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\User;
use App\Models\UserProgressPredictor;
use App\Repositories\PracticeSessionRepository;
use App\Repositories\UserLessonProgressRepository;

/**
 * Progress Predictor (IELTS Band Predictor).
 *
 * - recalculate(): hitung ulang cache setelah sesi latihan selesai (complete()).
 * - predictor(): baca cache; bila kosong, hitung on-the-fly lalu simpan.
 *
 * Overall Index = (Completion × 0.40) + (Mastery × 0.40) + (Accuracy × 0.20).
 */
class ProgressPredictorService
{
    public const REQUIRED_PASSED_LESSONS = 3;

    public const MASTERY_LAST_SESSIONS = 10;

    public const WEIGHT_COMPLETION = 0.40;

    public const WEIGHT_MASTERY = 0.40;

    public const WEIGHT_ACCURACY = 0.20;

    public function __construct(
        private readonly PracticeSessionRepository $sessions,
        private readonly UserLessonProgressRepository $progress,
    ) {
    }

    public function recalculate(User $user): void
    {
        $passedLessons = $this->progress->passedLessonCount($user->id);
        $totalLessons = Lesson::query()->where('is_active', true)->count();

        $completion = $totalLessons > 0
            ? round($passedLessons / $totalLessons * 100, 2)
            : 0.0;

        if ($passedLessons < self::REQUIRED_PASSED_LESSONS) {
            $this->persist($user, [
                'completion_score' => $completion,
                'mastery_score' => 0,
                'accuracy_score' => 0,
                'overall_index' => 0,
                'predicted_band' => 'NEED_MORE_DATA',
                'status_label' => 'Need More Data',
                'color_code' => '#9CA3AF',
                'passed_lessons_count' => $passedLessons,
                'is_ready' => false,
            ]);

            return;
        }

        $mastery = $this->masteryScore($user->id);
        $accuracy = $this->accuracyScore($user->id);
        $overall = round(
            $completion * self::WEIGHT_COMPLETION
            + $mastery * self::WEIGHT_MASTERY
            + $accuracy * self::WEIGHT_ACCURACY,
            2,
        );

        [$band, $label, $color] = $this->mapToBand($overall);

        $this->persist($user, [
            'completion_score' => $completion,
            'mastery_score' => $mastery,
            'accuracy_score' => $accuracy,
            'overall_index' => $overall,
            'predicted_band' => $band,
            'status_label' => $label,
            'color_code' => $color,
            'passed_lessons_count' => $passedLessons,
            'is_ready' => true,
        ]);
    }

    private function persist(User $user, array $attributes): void
    {
        UserProgressPredictor::updateOrCreate(
            ['user_id' => $user->id],
            array_merge($attributes, ['last_calculated_at' => now()]),
        );
    }

    private function masteryScore(int $userId): float
    {
        $scores = $this->sessions
            ->lastPassedSessions($userId, self::MASTERY_LAST_SESSIONS)
            ->pluck('score')
            ->map(fn ($score) => (float) $score);

        return $scores->isEmpty()
            ? 0.0
            : round($scores->avg(), 2);
    }

    private function accuracyScore(int $userId): float
    {
        [$total, $correct] = $this->sessions->passedSessionKeyPointTotals($userId);

        return $total > 0
            ? round($correct / $total * 100, 2)
            : 0.0;
    }

    /**
     * @return array{0:string, 1:string, 2:string}  [band, label, color]
     */
    private function mapToBand(float $overall): array
    {
        return match (true) {
            $overall >= 90 => ['Band 7.5 - 8.5', 'Expert / Exam Ready', '#10B981'],
            $overall >= 78 => ['Band 6.5 - 7.0', 'Competent / Target Achieved', '#22C55E'],
            $overall >= 65 => ['Band 5.5 - 6.0', 'Modest / Need More Practice', '#EAB308'],
            $overall >= 50 => ['Band 4.5 - 5.0', 'Limited / Focus on Key Points', '#F97316'],
            default => ['Band < 4.5', 'Beginner / Foundation Level', '#EF4444'],
        };
    }

    /**
     * Payload untuk GET /user/progress-predictor (baca cache; hitung bila kosong).
     */
    public function predictor(User $user): array
    {
        $cache = UserProgressPredictor::where('user_id', $user->id)->first();

        if (! $cache) {
            $this->recalculate($user);
            $cache = UserProgressPredictor::where('user_id', $user->id)->firstOrFail();
        }

        if (! $cache->is_ready) {
            return [
                'is_ready' => false,
                'predicted_band' => 'NEED_MORE_DATA',
                'overall_index' => 0.0,
                'status_label' => 'Need More Data',
                'color_code' => '#9CA3AF',
                'message' => 'Selesaikan minimal 3 Lesson untuk melihat prediksi IELTS Band Anda.',
                'passed_lessons_count' => (int) $cache->passed_lessons_count,
                'required_lessons_count' => self::REQUIRED_PASSED_LESSONS,
            ];
        }

        $totalLessons = Lesson::query()->where('is_active', true)->count();

        return [
            'is_ready' => true,
            'predicted_band' => $cache->predicted_band,
            'overall_index' => (float) $cache->overall_index,
            'status_label' => $cache->status_label,
            'color_code' => $cache->color_code,
            'metrics_breakdown' => [
                'completion_rate' => [
                    'score' => (float) $cache->completion_score,
                    'weight' => sprintf('%d%%', (int) (self::WEIGHT_COMPLETION * 100)),
                    'passed_lessons' => (int) $cache->passed_lessons_count,
                    'total_lessons' => $totalLessons,
                ],
                'mastery_performance' => [
                    'score' => (float) $cache->mastery_score,
                    'weight' => sprintf('%d%%', (int) (self::WEIGHT_MASTERY * 100)),
                    'based_on_last_sessions' => self::MASTERY_LAST_SESSIONS,
                ],
                'key_point_accuracy' => [
                    'score' => (float) $cache->accuracy_score,
                    'weight' => sprintf('%d%%', (int) (self::WEIGHT_ACCURACY * 100)),
                ],
            ],
            'last_updated' => $cache->last_calculated_at?->toIso8601String(),
        ];
    }
}