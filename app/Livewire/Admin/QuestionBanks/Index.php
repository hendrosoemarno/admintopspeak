<?php

namespace App\Livewire\Admin\QuestionBanks;

use App\Enums\CefrLevel;
use App\Enums\TestType;
use App\Models\QuestionBank;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $test_type = 'ADAPTIVE';

    public int $part_number = 1;

    public string $cefr_level = 'A1';

    public string $question_text = '';

    public string $standard_answer = '';

    public string $vocabTags = '';

    public bool $is_starter = false;

    public string $topic_category = 'General Conversation';

    public string $audioUrl = '';

    public string $search = '';

    public string $activeLevel = 'A1';

    public string $activeTopic = '';

    public string $activeTestType = 'ADAPTIVE';

    public ?int $activePart = null;

    protected function rules(): array
    {
        return [
            'test_type' => 'required|in:ADAPTIVE,IELTS_SPEAKING,TOEFL_IBT',
            'part_number' => 'required|integer|min:1|max:4',
            'cefr_level' => 'required|in:' . implode(',', array_map(fn ($l) => $l->value, CefrLevel::cases())),
            'question_text' => 'required|string|min:5|unique:question_banks,question_text,' . ($this->editingId ?? 'NULL'),
            'standard_answer' => 'required|string|min:10',
            'vocabTags' => 'nullable|string',
            'is_starter' => 'boolean',
            'topic_category' => 'required|string|max:100',
            'audioUrl' => 'nullable|url',
        ];
    }

    public function setLevel(?string $level): void
    {
        $this->activeLevel = $level ?? '';
        $this->activeTopic = '';
        $this->resetPage();
    }

    public function setTopic(?string $topic): void
    {
        $this->activeTopic = $topic ?? '';
        $this->resetPage();
    }

    public function setTestType(?string $testType): void
    {
        $this->activeTestType = $testType ?? 'ADAPTIVE';
        $this->activeLevel = '';
        $this->activeTopic = '';
        $this->activePart = null;
        $this->search = '';
        $this->resetPage();
    }

    public function setPart(?int $part): void
    {
        $this->activePart = $part;
        $this->activeLevel = '';
        $this->activeTopic = '';
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->test_type = in_array($this->activeTestType, ['IELTS_SPEAKING', 'TOEFL_IBT'], true)
            ? $this->activeTestType
            : 'ADAPTIVE';
        $this->showForm = true;
        $this->editingId = null;
    }

    public function openEdit(int $id): void
    {
        $q = QuestionBank::findOrFail($id);
        $this->editingId = $q->id;
        $this->test_type = $q->test_type->value;
        $this->part_number = $q->part_number;
        $this->cefr_level = $q->cefr_level->value;
        $this->question_text = $q->question_text;
        $this->standard_answer = $q->standard_answer ?? '';
        $this->vocabTags = implode(', ', $q->required_vocab_tags ?? []);
        $this->is_starter = $q->is_starter;
        $this->topic_category = $q->topic_category;
        $this->audioUrl = $q->metadata['audio_url'] ?? '';
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

        $tags = collect(explode(',', $this->vocabTags))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->values()
            ->all();

        $data = [
            'test_type' => $this->test_type,
            'part_number' => $this->part_number,
            'cefr_level' => $this->cefr_level,
            'question_text' => $this->question_text,
            'standard_answer' => $this->standard_answer,
            'required_vocab_tags' => $tags,
            'is_starter' => $this->is_starter,
            'topic_category' => $this->topic_category,
            'metadata' => ['audio_url' => $this->audioUrl ?: null],
        ];

        if ($this->editingId) {
            QuestionBank::findOrFail($this->editingId)->update($data);
            $this->dispatch('flash', message: 'Soal berhasil diperbarui.');
        } else {
            QuestionBank::create($data);
            $this->dispatch('flash', message: 'Soal baru berhasil ditambahkan.');
        }

        $this->closeForm();
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        QuestionBank::findOrFail($id)->delete();
        $this->dispatch('flash', message: 'Soal berhasil dihapus.');
    }

    public function toggleStarter(int $id): void
    {
        $q = QuestionBank::findOrFail($id);
        $q->update(['is_starter' => ! $q->is_starter]);
    }

    #[Computed]
    public function levels(): array
    {
        return CefrLevel::cases();
    }

    #[Computed]
    public function testTypes(): array
    {
        return TestType::cases();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->test_type = 'ADAPTIVE';
        $this->part_number = 1;
        $this->cefr_level = 'A1';
        $this->question_text = '';
        $this->standard_answer = '';
        $this->vocabTags = '';
        $this->is_starter = false;
        $this->topic_category = 'General Conversation';
        $this->audioUrl = '';
        $this->resetValidation();
    }

    #[On('flash')]
    public function flash(string $message): void
    {
        session()->flash('status', $message);
    }

    public function render()
    {
        $isExamTab = in_array($this->activeTestType, ['IELTS_SPEAKING', 'TOEFL_IBT'], true);

        $base = QuestionBank::query()
            ->when($this->activeTestType !== '', fn ($q) => $q->where('test_type', $this->activeTestType))
            ->when($this->activePart !== null, fn ($q) => $q->where('part_number', $this->activePart))
            ->when($this->activeLevel !== '', fn ($q) => $q->where('cefr_level', $this->activeLevel));

        $levelCounts = QuestionBank::query()
            ->when($this->activeTestType !== '', fn ($q) => $q->where('test_type', $this->activeTestType))
            ->select('cefr_level')
            ->get()
            ->groupBy(fn ($q) => $q->cefr_level->value)
            ->map->count();

        $testTypeCounts = QuestionBank::query()
            ->select('test_type')
            ->get()
            ->groupBy(fn ($q) => $q->test_type->value)
            ->map->count();

        $partCounts = QuestionBank::query()
            ->when($this->activeTestType !== '', fn ($q) => $q->where('test_type', $this->activeTestType))
            ->select('part_number')
            ->get()
            ->groupBy('part_number')
            ->map->count();

        $topics = (clone $base)
            ->selectRaw('topic_category, COUNT(*) as total')
            ->groupBy('topic_category')
            ->orderByDesc('total')
            ->orderBy('topic_category')
            ->get();

        $questions = (clone $base)
            ->when($this->activeTopic !== '', fn ($q) => $q->where('topic_category', $this->activeTopic))
            ->when($this->search, fn ($q) => $q->where('question_text', 'like', "%{$this->search}%"))
            ->withCount('conversationLogs')
            ->orderByDesc('is_starter')
            ->orderByDesc('id')
            ->paginate(12);

        $topicTotal = (clone $base)
            ->when($this->activeTopic !== '', fn ($q) => $q->where('topic_category', $this->activeTopic))
            ->count();

        return view('livewire.admin.question-banks.index', [
            'questions' => $questions,
            'topics' => $topics,
            'levelCounts' => $levelCounts,
            'testTypeCounts' => $testTypeCounts,
            'partCounts' => $partCounts,
            'topicTotal' => $topicTotal,
            'isExamTab' => $isExamTab,
        ])->layout('layouts.app', ['title' => 'Question Banks']);
    }
}