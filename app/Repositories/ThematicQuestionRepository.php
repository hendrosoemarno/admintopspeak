<?php

namespace App\Repositories;

use App\Models\ThematicQuestion;

class ThematicQuestionRepository extends Repository
{
    protected function model(): string
    {
        return ThematicQuestion::class;
    }

    public function randomForTopic(int $topicId, array $excludeIds = []): ?ThematicQuestion
    {
        return $this->query()
            ->where('topic_id', $topicId)
            ->whereNotIn('id', $excludeIds)
            ->inRandomOrder()
            ->first();
    }

    public function countForTopic(int $topicId): int
    {
        return $this->query()->where('topic_id', $topicId)->count();
    }
}