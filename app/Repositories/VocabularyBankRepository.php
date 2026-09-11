<?php

namespace App\Repositories;

use App\Models\VocabularyBank;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class VocabularyBankRepository extends Repository
{
    protected function model(): string
    {
        return VocabularyBank::class;
    }

    public function listPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->query()
            ->when(! empty($filters['q']), fn ($query) => $query->where('word', 'like', '%'.$filters['q'].'%'))
            ->when(! empty($filters['cefr_level']), fn ($query) => $query->where('cefr_level', $filters['cefr_level']))
            ->when(! empty($filters['topic_category']), fn ($query) => $query->where('topic_category', $filters['topic_category']))
            ->orderBy('word')
            ->paginate($perPage);
    }
}