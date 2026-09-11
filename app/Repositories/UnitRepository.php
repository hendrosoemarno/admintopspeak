<?php

namespace App\Repositories;

use App\Models\Unit;

class UnitRepository extends Repository
{
    protected function model(): string
    {
        return Unit::class;
    }

    public function curriculum(): mixed
    {
        return $this->query()
            ->with(['lessons' => fn ($q) => $q->orderBy('lesson_number')->withCount('questions')])
            ->orderBy('part')
            ->orderBy('unit_number')
            ->get();
    }
}