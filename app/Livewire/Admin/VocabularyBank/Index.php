<?php

namespace App\Livewire\Admin\VocabularyBank;

use App\Enums\CefrLevel;
use App\Models\VocabularyBank;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $word = '';

    public string $part_of_speech = 'noun';

    public string $cefr_level = 'A1';

    public string $topic_category = '';

    public string $search = '';

    public string $levelFilter = '';

    public string $posFilter = '';

    public string $topicFilter = '';

    public string $sortColumn = 'word';

    public string $sortDirection = 'asc';

    public array $partsOfSpeech = ['noun', 'verb', 'adjective', 'adverb', 'phrase', 'idiom'];

    public array $sortableColumns = ['word', 'part_of_speech', 'cefr_level', 'topic_category'];

    protected function rules(): array
    {
        return [
            'word' => 'required|string|max:100',
            'part_of_speech' => 'required|in:' . implode(',', $this->partsOfSpeech),
            'cefr_level' => 'required|in:' . implode(',', array_map(fn ($l) => $l->value, CefrLevel::cases())),
            'topic_category' => 'required|string|max:100',
        ];
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->levelFilter = '';
        $this->posFilter = '';
        $this->topicFilter = '';
        $this->resetPage();
    }

    public function sortBy(string $column): void
    {
        if (! in_array($column, $this->sortableColumns, true)) {
            return;
        }

        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
        $this->editingId = null;
    }

    public function openEdit(int $id): void
    {
        $vocab = VocabularyBank::findOrFail($id);
        $this->editingId = $vocab->id;
        $this->word = $vocab->word;
        $this->part_of_speech = $vocab->part_of_speech;
        $this->cefr_level = $vocab->cefr_level;
        $this->topic_category = $vocab->topic_category;
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'word' => strtolower(trim($this->word)),
            'part_of_speech' => $this->part_of_speech,
            'cefr_level' => $this->cefr_level,
            'topic_category' => $this->topic_category,
        ];

        if ($this->editingId) {
            VocabularyBank::findOrFail($this->editingId)->update($data);
            $this->dispatch('flash', message: 'Kata diperbarui.');
        } else {
            VocabularyBank::create($data);
            $this->dispatch('flash', message: 'Kata ditambahkan.');
        }

        $this->closeForm();
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        VocabularyBank::findOrFail($id)->delete();
        $this->dispatch('flash', message: 'Kata dihapus.');
    }

    #[Computed]
    public function levels(): array
    {
        return CefrLevel::cases();
    }

    public function sortIcon(string $column): string
    {
        if ($this->sortColumn !== $column) {
            return '⇅';
        }

        return $this->sortDirection === 'asc' ? '↑' : '↓';
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->word = '';
        $this->part_of_speech = 'noun';
        $this->cefr_level = 'A1';
        $this->topic_category = '';
        $this->resetValidation();
    }

    #[On('flash')]
    public function flash(string $message): void
    {
        session()->flash('status', $message);
    }

    public function render()
    {
        $topics = VocabularyBank::query()
            ->select('topic_category')
            ->distinct()
            ->orderBy('topic_category')
            ->pluck('topic_category');

        $vocabulary = VocabularyBank::query()
            ->when($this->levelFilter !== '', fn ($q) => $q->where('cefr_level', $this->levelFilter))
            ->when($this->posFilter !== '', fn ($q) => $q->where('part_of_speech', $this->posFilter))
            ->when($this->topicFilter !== '', fn ($q) => $q->where('topic_category', $this->topicFilter))
            ->when($this->search, fn ($q) => $q->where('word', 'like', "%{$this->search}%"))
            ->orderBy($this->sortColumn, $this->sortDirection)
            ->paginate(15);

        return view('livewire.admin.vocabulary-bank.index', [
            'vocabulary' => $vocabulary,
            'topics' => $topics,
        ])->layout('layouts.app', ['title' => 'Vocabulary Bank']);
    }
}
