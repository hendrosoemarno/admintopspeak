<?php

namespace App\Repositories;

use App\Models\ConversationLog;

class ConversationLogRepository extends Repository
{
    protected function model(): string
    {
        return ConversationLog::class;
    }

    public function log(array $attributes): ConversationLog
    {
        return $this->query()->create($attributes);
    }

    public function logTurn(
        int $userId,
        string $sessionId,
        int $turnNumber,
        ?int $questionId,
        string $transcript,
        ?string $correctWay,
        array $scores,
        ?int $curriculumQuestionId = null,
        ?int $curriculumScore = null,
        bool $keyPointDetected = false,
        ?string $keyPointTarget = null,
        ?string $grammarFeedback = null,
        ?string $vocabularyFeedback = null,
        ?string $suggestedAnswer = null,
        bool $normalOnError = false,
    ): ConversationLog {
        $attributes = [
            'user_id' => $userId,
            'session_id' => $sessionId,
            'turn_number' => $turnNumber,
            'user_response_text' => $transcript,
            'score_word_count' => $scores['word_count_score'],
            'score_grammar' => $scores['grammar_score'],
            'total_turn_score' => $scores['total_turn_score'],
            'has_error' => $scores['has_error'],
            'correct_way_text' => $correctWay,
        ];

        if ($curriculumQuestionId !== null) {
            $attributes['curriculum_question_id'] = $curriculumQuestionId;
        } else {
            $attributes['question_id'] = $questionId;
        }

        if ($curriculumScore !== null) {
            $attributes['curriculum_score'] = $curriculumScore;
            $attributes['key_point_detected'] = $keyPointDetected;
            $attributes['key_point_target'] = $keyPointTarget;
            $attributes['grammar_feedback'] = $grammarFeedback;
            $attributes['vocabulary_feedback'] = $vocabularyFeedback;
        }

        if ($suggestedAnswer !== null) {
            $attributes['suggested_answer'] = $suggestedAnswer;
        }

        if ($normalOnError || ! $scores['has_error']) {
            $attributes['step_state'] = 'NORMAL';
        } else {
            $attributes['step_state'] = 'WAITING_REPETITION';
            $attributes['user_said_text'] = $transcript;
            $attributes['expected_repetition_text'] = $correctWay;
        }

        if ($this->hasTurn($userId, $sessionId, $turnNumber)) {
            $this->updateTurn($userId, $sessionId, $turnNumber, $attributes);

            return (new ConversationLog)
                ->newQuery()
                ->where('user_id', $userId)
                ->where('session_id', $sessionId)
                ->where('turn_number', $turnNumber)
                ->firstOrFail();
        }

        return $this->log($attributes);
    }

    public function findTurn(int $userId, string $sessionId, int $turnNumber): ?ConversationLog
    {
        return $this->query()
            ->where('user_id', $userId)
            ->where('session_id', $sessionId)
            ->where('turn_number', $turnNumber)
            ->first();
    }

    public function markRepetitionResult(
        ConversationLog $log,
        bool $success,
        int $attempts,
    ): void {
        $log->update([
            'step_state' => 'NORMAL',
            'repetition_attempts' => $attempts,
            'repetition_success' => $success,
        ]);
    }

    public function sessionLogs(int $userId, string $sessionId): \Illuminate\Support\Collection
    {
        return $this->query()
            ->where('user_id', $userId)
            ->where('session_id', $sessionId)
            ->orderBy('turn_number')
            ->get();
    }

    public function latestTurnForSession(int $userId, string $sessionId): ?ConversationLog
    {
        return $this->query()
            ->where('user_id', $userId)
            ->where('session_id', $sessionId)
            ->orderByDesc('turn_number')
            ->first();
    }

    public function hasTurn(int $userId, string $sessionId, int $turnNumber): bool
    {
        return $this->query()
            ->where('user_id', $userId)
            ->where('session_id', $sessionId)
            ->where('turn_number', $turnNumber)
            ->exists();
    }

    public function updateTurn(int $userId, string $sessionId, int $turnNumber, array $attributes): void
    {
        $this->query()
            ->where('user_id', $userId)
            ->where('session_id', $sessionId)
            ->where('turn_number', $turnNumber)
            ->update($attributes);
    }
}