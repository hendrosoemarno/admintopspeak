<?php

namespace App\Livewire\Admin\ThematicTopics;

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

    public string $topic_name = '';

    public string $roleplay_persona = '';

    public string $selected_level = 'Beginner';

    public string $vocabTags = '';

    public bool $is_active = true;

    public string $search = '';

    public array $levels = ['Beginner', 'Elementary', 'Intermediate', 'Upper-Intermediate', 'Advanced', 'Proficiency'];

    protected function rules(): array
    {
        return [
            'topic_name' => 'required|string|max:150|unique:thematic_topics,topic_name,' . ($this->editingId ?? 'NULL'),
            'roleplay_persona' => 'required|string',
            'selected_level' => 'required|string|max:50',
            'vocabTags' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
        $this->editingId = null;
    }

    public function openEdit(int $id): void
    {
        $topic = ThematicTopic::findOrFail($id);
        $this->editingId = $topic->id;
        $this->topic_name = $topic->topic_name;
        $this->roleplay_persona = $topic->roleplay_persona;
        $this->selected_level = $topic->selected_level;
        $this->vocabTags = implode(', ', $topic->context_vocab_tags ?? []);
        $this->is_active = $topic->is_active;
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

        $tags = collect(explode(',', $this->vocabTags))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->values()
            ->all();

        $data = [
            'topic_name' => $this->topic_name,
            'roleplay_persona' => $this->roleplay_persona,
            'selected_level' => $this->selected_level,
            'context_vocab_tags' => $tags,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            ThematicTopic::findOrFail($this->editingId)->update($data);
            $this->dispatch('flash', message: 'Topik diperbarui.');
        } else {
            $data['created_by'] = null;
            ThematicTopic::create($data);
            $this->dispatch('flash', message: 'Topik ditambahkan.');
        }

        $this->closeForm();
        $this->resetPage();
    }

    public function toggleActive(int $id): void
    {
        $topic = ThematicTopic::findOrFail($id);
        $topic->update(['is_active' => ! $topic->is_active]);
    }

    public function delete(int $id): void
    {
        ThematicTopic::findOrFail($id)->delete();
        $this->dispatch('flash', message: 'Topik dihapus.');
    }

    #[Computed]
    public function vocabWords(): array
    {
        return \App\Models\VocabularyBank::query()->limit(100)->pluck('word')->all();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->topic_name = '';
        $this->roleplay_persona = '';
        $this->selected_level = 'Beginner';
        $this->vocabTags = '';
        $this->is_active = true;
        $this->resetValidation();
    }

    #[On('flash')]
    public function flash(string $message): void
    {
        session()->flash('status', $message);
    }

    public function render()
    {
        $topics = ThematicTopic::query()
            ->with('creator')
            ->when($this->search, fn ($q) => $q->where(function ($inner) {
                $inner->where('topic_name', 'like', "%{$this->search}%")
                    ->orWhere('roleplay_persona', 'like', "%{$this->search}%");
            }))
            ->latest()
            ->paginate(12);

        return view('livewire.admin.thematic-topics.index', ['topics' => $topics])
            ->layout('layouts.app', ['title' => 'Thematic Topics']);
    }
}