<?php

namespace App\Repositories;

use App\Models\QuestionBank;

class QuestionBankRepository extends Repository
{
    protected function model(): string
    {
        return QuestionBank::class;
    }

    public function starterForLevel(string $cefrLevel, array $excludeIds = []): ?QuestionBank
    {
        return $this->query()
            ->where('cefr_level', $cefrLevel)
            ->where('is_starter', true)
            ->whereNotIn('id', $excludeIds)
            ->inRandomOrder()
            ->first();
    }

    public function randomForLevel(string $cefrLevel, array $excludeIds = []): ?QuestionBank
    {
        return $this->query()
            ->where('cefr_level', $cefrLevel)
            ->whereNotIn('id', $excludeIds)
            ->inRandomOrder()
            ->first();
    }

    public function randomForTopicCategory(string $topicCategory, array $excludeIds = [], ?string $cefrLevel = null): ?QuestionBank
    {
        $query = $this->query()
            ->where('topic_category', $topicCategory)
            ->whereNotIn('id', $excludeIds);

        if ($cefrLevel !== null) {
            $query->where('cefr_level', $cefrLevel);
        }

        return $query->inRandomOrder()->first();
    }

    public function nextForTurn(string $cefrLevel, array $excludeIds = [], bool $preferStarter = false): ?QuestionBank
    {
        if ($preferStarter) {
            return $this->starterForLevel($cefrLevel, $excludeIds)
                ?? $this->randomForLevel($cefrLevel, $excludeIds);
        }

        return $this->randomForLevel($cefrLevel, $excludeIds);
    }

    /**
     * Soal ujian (IELTS/TOEFL) per part berurutan.
     * Turn 1..N memetakan ke soal terurut part_number asc, lalu random per part.
     */
    public function nextForExam(string $testType, array $excludeIds = [], int $preferredPart = 1): ?QuestionBank
    {
        $query = $this->query()
            ->where('test_type', $testType)
            ->whereNotIn('id', $excludeIds);

        // Prefer part yang lebih rendah dulu (part berurutan), random dalam part.
        $candidate = (clone $query)
            ->where('part_number', '>=', $preferredPart)
            ->orderBy('part_number')
            ->inRandomOrder()
            ->first();

        return $candidate ?? (clone $query)
            ->orderBy('part_number')
            ->inRandomOrder()
            ->first();
    }

    public function partsForTest(string $testType): array
    {
        return $this->query()
            ->where('test_type', $testType)
            ->select('part_number')
            ->distinct()
            ->orderBy('part_number')
            ->pluck('part_number')
            ->all();
    }

    public function countForTest(string $testType): int
    {
        return $this->query()->where('test_type', $testType)->count();
    }
}