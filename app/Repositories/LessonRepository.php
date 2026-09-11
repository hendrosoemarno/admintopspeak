<?php

namespace App\Repositories;

use App\Models\Lesson;

class LessonRepository extends Repository
{
    protected function model(): string
    {
        return Lesson::class;
    }

    public function forUnit(int $unitId): mixed
    {
        return $this->query()
            ->where('unit_id', $unitId)
            ->withCount('questions')
            ->orderBy('lesson_number')
            ->get();
    }
}