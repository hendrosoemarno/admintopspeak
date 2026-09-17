<?php

namespace App\Livewire\Admin\ThematicQuestions;

use App\Enums\CefrLevel;
use App\Models\ThematicQuestion;
use App\Models\ThematicTopic;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public ?int $editingId = null;

    public ?int $topic_id = null;

    public string $question_text = '';

    public string $standard_answer = '';

    public string $cefr_level = 'A1';

    public string $key_point = '';

    public string $search = '';

    public ?int $activeTopicId = null;

    public string $activeLevel = '';

    protected function rules(): array
    {
        return [
            'topic_id' => 'required|exists:thematic_topics,id',
            'question_text' => 'required|string|min:5|unique:thematic_questions,question_text,' . ($this->editingId ?? 'NULL'),
            'standard_answer' => 'required|string|min:10',
            'cefr_level' => 'required|in:' . implode(',', array_map(fn ($l) => $l->value, CefrLevel::cases())),
            'key_point' => 'nullable|string|max:100',
        ];
    }

    public function setTopic(?int $topicId): void
    {
        $this->activeTopicId = $topicId;
        $this->activeLevel = '';
        $this->resetPage();
    }

    public function setLevel(?string $level): void
    {
        $this->activeLevel = $level ?? '';
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->topic_id = $this->activeTopicId ?? $this->topics->first()->id ?? null;
        $this->showForm = true;
        $this->editingId = null;
    }

    public function openEdit(int $id): void
    {
        $q = ThematicQuestion::findOrFail($id);
        $this->editingId = $q->id;
        $this->topic_id = $q->topic_id;
        $this->question_text = $q->question_text;
        $this->standard_answer = $q->standard_answer ?? '';
        $this->cefr_level = $q->cefr_level->value;
        $this->key_point = $q->key_point ?? '';
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

        $thresholds = ['A1' => 12, 'A2' => 20, 'B1' => 35, 'B2' => 55, 'C1' => 75, 'C2' => 90];
        $wordCount = str_word_count($this->standard_answer);
        $required = $thresholds[$this->cefr_level] ?? 12;

        if ($wordCount < $required) {
            $this->addError('standard_answer', "Jawaban standar minimal {$required} kata (saat ini {$wordCount} kata) untuk level {$this->cefr_level}.");

            return;
        }

        $data = [
            'topic_id' => $this->topic_id,
            'question_text' => $this->question_text,
            'standard_answer' => $this->standard_answer,
            'cefr_level' => $this->cefr_level,
            'key_point' => $this->key_point ?: null,
        ];

        if ($this->editingId) {
            ThematicQuestion::findOrFail($this->editingId)->update($data);
            $this->dispatch('flash', message: 'Soal tematik berhasil diperbarui.');
        } else {
            ThematicQuestion::create($data);
            $this->dispatch('flash', message: 'Soal tematik baru berhasil ditambahkan.');
        }

        $this->closeForm();
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        ThematicQuestion::findOrFail($id)->delete();
        $this->dispatch('flash', message: 'Soal tematik berhasil dihapus.');
    }

    #[Computed]
    public function topics()
    {
        return ThematicTopic::query()
            ->withCount('questions')
            ->orderBy('topic_name')
            ->get();
    }

    #[Computed]
    public function levels(): array
    {
        return CefrLevel::cases();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->topic_id = null;
        $this->question_text = '';
        $this->standard_answer = '';
        $this->cefr_level = 'A1';
        $this->key_point = '';
        $this->resetValidation();
    }

    #[On('flash')]
    public function flash(string $message): void
    {
        session()->flash('status', $message);
    }

    public function render()
    {
        $topicCounts = ThematicQuestion::query()
            ->selectRaw('topic_id, COUNT(*) as total')
            ->groupBy('topic_id')
            ->pluck('total', 'topic_id');

        $levelCounts = ThematicQuestion::query()
            ->when($this->activeTopicId !== null, fn ($q) => $q->where('topic_id', $this->activeTopicId))
            ->select('cefr_level')
            ->get()
            ->groupBy(fn ($q) => $q->cefr_level->value)
            ->map->count();

        $questions = ThematicQuestion::query()
            ->with('topic')
            ->when($this->activeTopicId !== null, fn ($q) => $q->where('topic_id', $this->activeTopicId))
            ->when($this->activeLevel !== '', fn ($q) => $q->where('cefr_level', $this->activeLevel))
            ->when($this->search, fn ($q) => $q->where(function ($inner) {
                $inner->where('question_text', 'like', "%{$this->search}%")
                    ->orWhere('key_point', 'like', "%{$this->search}%");
            }))
            ->latest()
            ->paginate(12);

        $topicTotal = ThematicQuestion::query()
            ->when($this->activeTopicId !== null, fn ($q) => $q->where('topic_id', $this->activeTopicId))
            ->when($this->activeLevel !== '', fn ($q) => $q->where('cefr_level', $this->activeLevel))
            ->when($this->search, fn ($q) => $q->where('question_text', 'like', "%{$this->search}%"))
            ->count();

        return view('livewire.admin.thematic-questions.index', [
            'questions' => $questions,
            'topicCounts' => $topicCounts,
            'levelCounts' => $levelCounts,
            'activeTopic' => $this->activeTopicId !== null ? ThematicTopic::find($this->activeTopicId) : null,
            'topicTotal' => $topicTotal,
        ])->layout('layouts.app', ['title' => 'Soal Tematik']);
    }
}