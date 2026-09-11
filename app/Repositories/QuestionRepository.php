<?php

namespace App\Repositories;

use App\Models\Lesson;
use App\Models\Question;

class QuestionRepository extends Repository
{
    protected function model(): string
    {
        return Question::class;
    }

    /**
     * 5 soal acak dari sebuah lesson (spesifikasi IELTS.md).
     */
    public function randomForLesson(int $lessonId, int $limit = 5): mixed
    {
        return $this->query()
            ->where('lesson_id', $lessonId)
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    public function findBelongingTo(int $lessonId, array $questionIds): mixed
    {
        return $this->query()
            ->where('lesson_id', $lessonId)
            ->whereIn('id', $questionIds)
            ->get();
    }

    /**
     * Nomor part (1/2/3) untuk mode IELTS Speaking berdasarkan unit-unit
     * kurikulum yang sudah memiliki minimal satu soal. Bila lesson diberikan,
     * part mengikuti unit dari lesson tersebut (kursus terbatas per lesson).
     */
    public function partsForIelts(?int $lessonId = null): array
    {
        if ($lessonId !== null) {
            $part = Lesson::find($lessonId)?->unit?->part;

            return $part !== null ? [(int) $part] : [];
        }

        return $this->query()
            ->join('lessons', 'questions.lesson_id', '=', 'lessons.id')
            ->join('units', 'lessons.unit_id', '=', 'units.id')
            ->distinct()
            ->orderBy('units.part')
            ->pluck('units.part')
            ->map(fn ($part) => (int) $part)
            ->all();
    }

    /**
     * Satu soal acak dari unit part tertentu. Bila lesson diberikan, pemilihan
     * dibatasi pada lesson tersebut (sesi IELTS berbasis kursus).
     */
    public function randomForPart(int $part, array $excludeIds = [], ?int $lessonId = null): ?Question
    {
        $query = $this->query()->whereNotIn('id', $excludeIds);

        if ($lessonId !== null) {
            $query->where('lesson_id', $lessonId);
        } else {
            $query->whereHas('lesson.unit', fn ($q) => $q->where('units.part', $part));
        }

        return $query->with('lesson.unit')->inRandomOrder()->first();
    }
}