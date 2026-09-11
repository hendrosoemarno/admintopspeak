<?php

namespace App\Services;

use App\Enums\LessonProgressStatus;
use App\Exceptions\LessonNotFoundException;
use App\Models\User;
use App\Repositories\CurriculumEvaluationLogRepository;
use App\Repositories\LessonRepository;
use App\Repositories\PracticeSessionRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\UnitRepository;
use App\Repositories\UserLessonProgressRepository;
use Illuminate\Support\Str;

/**
 * Orkestrator fitur kurikulum IELTS (Units/Lessons/Questions).
 *
 * - curriculum(): daftar unit & lesson beserta status kelulusan per user.
 * - session(): ambil 5 soal acak dari sebuah lesson.
 * - evaluateQuestion(): nilai 1 jawaban, simpan ke curriculum_evaluation_logs.
 * - complete(): gabungkan hasil evaluasi, hitung passing grade (>= 4 dari 5),
 *   lalu perbarui status user_lesson_progress.
 */
class CurriculumService
{
    public const SESSION_QUESTION_LIMIT = 5;

    /** Passing grade: minimal 4 dari 5 soal benar. */
    public const REQUIRED_CORRECT = 4;

    public function __construct(
        private readonly UnitRepository $units,
        private readonly LessonRepository $lessons,
        private readonly QuestionRepository $questions,
        private readonly UserLessonProgressRepository $progress,
        private readonly CurriculumEvaluationLogRepository $evalLogs,
        private readonly LessonEvaluator $evaluator,
        private readonly PracticeSessionRepository $practiceSessions,
        private readonly ProgressPredictorService $predictor,
    ) {
    }

    public function curriculum(User $user): array
    {
        $statusMap = $this->progress->statusMapForUser($user->id);

        return $this->units->curriculum()
            ->map(function ($unit) use ($statusMap) {
                return [
                    'unit_id' => $unit->id,
                    'unit_number' => $unit->unit_number,
                    'unit_title' => $unit->title,
                    'part' => $unit->part,
                    'outcome' => $unit->outcome,
                    'lessons' => $unit->lessons->map(fn ($lesson) => [
                        'lesson_id' => $lesson->id,
                        'lesson_number' => $lesson->lesson_number,
                        'lesson_title' => $lesson->title,
                        'difficulty' => $lesson->difficulty->value,
                        'status' => $statusMap[$lesson->id] ?? LessonProgressStatus::NOT_PASSED->value,
                    ])->values(),
                ];
            })
            ->values()
            ->all();
    }

    public function session(User $user, int $lessonId): array
    {
        $lesson = $this->lessons->find($lessonId) ?? throw LessonNotFoundException::forLesson();

        $selected = $this->questions->randomForLesson($lessonId, self::SESSION_QUESTION_LIMIT);

        $sessionId = 'SESS-'.strtoupper(Str::random(8));

        $this->practiceSessions->start($user->id, $lessonId, $sessionId);

        return [
            'session_id' => $sessionId,
            'lesson_id' => $lessonId,
            'questions' => $selected->map(fn ($q) => [
                'question_id' => $q->id,
                'question_text' => $q->question_text,
                'key_point' => $q->key_point,
            ])->values()->all(),
        ];
    }

    /**
     * Nilai 1 jawaban dan simpan ke curriculum_evaluation_logs.
     */
    public function evaluateQuestion(User $user, int $lessonId, array $data): array
    {
        $lesson = $this->lessons->find($lessonId) ?? throw LessonNotFoundException::forLesson();

        $question = $this->questions->find($data['question_id']);
        if (! $question || $question->lesson_id !== $lessonId) {
            throw new \InvalidArgumentException('question_id tidak valid atau bukan milik lesson ini.', 422);
        }

        if ($this->evalLogs->existsForSessionAndQuestion($data['session_id'], $question->id)) {
            throw new \InvalidArgumentException('Soal ini sudah dievaluasi pada sesi ini.', 422);
        }

        $transcript = $data['user_transcript'];
        $result = $this->evaluator->evaluateMany($question, [$transcript])[0];

        $suggestedAnswer = $result['is_correct'] ? '' : (string) ($result['suggested_answer'] ?? '');

        $log = $this->evalLogs->store([
            'user_id' => $user->id,
            'session_id' => $data['session_id'],
            'lesson_id' => $lessonId,
            'question_id' => $question->id,
            'user_transcript' => $transcript,
            'score' => $result['score'],
            'is_correct' => $result['is_correct'],
            'key_point_detected' => $result['key_point_detected'],
            'key_point_target' => $result['key_point_target'],
            'grammar_feedback' => $result['grammar_feedback'],
            'vocabulary_feedback' => $result['vocabulary_feedback'],
            'suggested_answer' => $suggestedAnswer,
        ]);

        return [
            'question_id' => $log->question_id,
            'is_correct' => $log->is_correct,
            'score' => $log->score,
            'key_point_detected' => $log->key_point_detected,
            'key_point_target' => $log->key_point_target,
            'grammar_feedback' => $log->grammar_feedback,
            'vocabulary_feedback' => $log->vocabulary_feedback,
            'suggested_answer' => $log->suggested_answer,
        ];
    }

    /**
     * Gabungkan hasil evaluasi per-soal, hitung passing grade, perbarui status lesson.
     */
    public function complete(User $user, int $lessonId, string $sessionId): array
    {
        $this->lessons->find($lessonId) ?? throw LessonNotFoundException::forLesson();

        $logs = $this->evalLogs->findBySession($sessionId, $lessonId);

        if ($logs->isEmpty()) {
            throw new \InvalidArgumentException('Belum ada evaluasi untuk sesi ini.', 422);
        }

        $evaluations = $logs->map(fn ($log) => [
            'question_id' => $log->question_id,
            'is_correct' => $log->is_correct,
            'score' => $log->score,
            'key_point_detected' => $log->key_point_detected,
            'key_point_target' => $log->key_point_target,
            'grammar_feedback' => $log->grammar_feedback,
            'vocabulary_feedback' => $log->vocabulary_feedback,
            'suggested_answer' => $log->suggested_answer,
        ])->all();

        $correctCount = collect($evaluations)->where('is_correct', true)->count();
        $total = count($evaluations);
        $isPassed = $correctCount >= self::REQUIRED_CORRECT;

        // Status hanya naik ke PASSED (tidak pernah diturunkan).
        if ($isPassed) {
            $this->progress->updateOrCreateStatus($user->id, $lessonId, LessonProgressStatus::PASSED);
        } else {
            $existing = $this->progress->statusMapForUser($user->id)[$lessonId] ?? null;
            if ($existing !== LessonProgressStatus::PASSED->value) {
                $this->progress->updateOrCreateStatus($user->id, $lessonId, LessonProgressStatus::NOT_PASSED);
            }
        }

        $this->recordPracticeSession($user->id, $lessonId, $sessionId, $evaluations, $correctCount, $total, $isPassed);

        $this->predictor->recalculate($user);

        return [
            'session_result' => [
                'correct_count' => $correctCount,
                'total_questions' => $total,
                'is_passed' => $isPassed,
                'lesson_status' => $isPassed ? LessonProgressStatus::PASSED->value : LessonProgressStatus::NOT_PASSED->value,
            ],
            'evaluations' => $evaluations,
        ];
    }

    public function userLessonProgress(User $user, int $lessonId): string
    {
        return $this->progress->statusMapForUser($user->id)[$lessonId]
            ?? LessonProgressStatus::NOT_PASSED->value;
    }

    /**
     * Simpan/update metrik practice_sessions hasil complete sebuah sesi.
     *
     * @param  array<int, array>  $evaluations
     */
    private function recordPracticeSession(
        int $userId,
        int $lessonId,
        string $sessionId,
        array $evaluations,
        int $correctCount,
        int $total,
        bool $isPassed,
    ): void {
        $score = collect($evaluations)->avg('score');
        $score = $score === null ? 0.0 : round((float) $score, 2);

        $evaluation = collect($evaluations);
        $totalKeyPoints = $evaluation->filter(fn ($e) => filled($e['key_point_target']))->count();
        $correctKeyPoints = $evaluation
            ->filter(fn ($e) => filled($e['key_point_target']) && $e['key_point_detected'])
            ->count();

        $this->practiceSessions->completeAttempt($userId, $sessionId, [
            'lesson_id' => $lessonId,
            'total_questions' => $total,
            'correct_count' => $correctCount,
            'score' => $score,
            'is_passed' => $isPassed,
            'total_keypoints' => $totalKeyPoints,
            'correct_keypoints' => $correctKeyPoints,
            'completed_at' => now(),
        ]);
    }
}