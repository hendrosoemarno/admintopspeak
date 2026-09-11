<?php

namespace App\Repositories;

use App\Models\FillerWord;

class FillerWordRepository extends Repository
{
    protected function model(): string
    {
        return FillerWord::class;
    }

    public function allActive(): \Illuminate\Support\Collection
    {
        return $this->query()->where('is_active', true)->get();
    }

    /** Daftar phrase filler aktif (diurutkan panjang-menurun agar regex tidak saling menelan). */
    public function activePhrases(): array
    {
        return $this->allActive()
            ->pluck('phrase')
            ->map(fn ($p) => mb_strtolower(trim((string) $p)))
            ->filter()
            ->sortByDesc(fn ($p) => mb_strlen($p))
            ->values()
            ->all();
    }
}
