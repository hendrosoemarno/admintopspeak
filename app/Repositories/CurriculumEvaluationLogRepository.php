<?php

namespace App\Repositories;

use App\Models\CurriculumEvaluationLog;
use Illuminate\Database\Eloquent\Collection;

class CurriculumEvaluationLogRepository extends Repository
{
    protected function model(): string
    {
        return CurriculumEvaluationLog::class;
    }

    public function store(array $data): CurriculumEvaluationLog
    {
        return $this->query()->create($data);
    }

    public function findBySession(string $sessionId, int $lessonId): Collection
    {
        return $this->query()
            ->where('session_id', $sessionId)
            ->where('lesson_id', $lessonId)
            ->orderBy('id')
            ->get();
    }

    public function existsForSessionAndQuestion(string $sessionId, int $questionId): bool
    {
        return $this->query()
            ->where('session_id', $sessionId)
            ->where('question_id', $questionId)
            ->exists();
    }

    public function countBySessionAndLesson(string $sessionId, int $lessonId): int
    {
        return $this->query()
            ->where('session_id', $sessionId)
            ->where('lesson_id', $lessonId)
            ->count();
    }
}