<?php

namespace App\Livewire\Admin\FillerWords;

use App\Models\FillerWord;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $phrase = '';

    public string $category = 'hesitation';

    public bool $is_active = true;

    public string $search = '';

    public ?string $categoryFilter = '';

    public array $categories = ['hesitation', 'discourse', 'phrase'];

    protected function rules(): array
    {
        return [
            'phrase' => 'required|string|max:100',
            'category' => 'required|in:' . implode(',', $this->categories),
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
        $word = FillerWord::findOrFail($id);
        $this->editingId = $word->id;
        $this->phrase = $word->phrase;
        $this->category = $word->category;
        $this->is_active = $word->is_active;
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
            'phrase' => strtolower(trim($this->phrase)),
            'category' => $this->category,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            FillerWord::findOrFail($this->editingId)->update($data);
            $this->dispatch('flash', message: 'Filler word diperbarui.');
        } else {
            FillerWord::create($data);
            $this->dispatch('flash', message: 'Filler word ditambahkan.');
        }

        $this->closeForm();
        $this->resetPage();
    }

    public function toggleActive(int $id): void
    {
        $word = FillerWord::findOrFail($id);
        $word->update(['is_active' => ! $word->is_active]);
    }

    public function delete(int $id): void
    {
        FillerWord::findOrFail($id)->delete();
        $this->dispatch('flash', message: 'Filler word dihapus.');
    }

    #[Computed]
    public function categoryLabels(): array
    {
        return [
            'hesitation' => 'Hesitation',
            'discourse' => 'Discourse',
            'phrase' => 'Phrase',
        ];
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->phrase = '';
        $this->category = 'hesitation';
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
        $fillerWords = FillerWord::query()
            ->when($this->search, fn ($q) => $q->where('phrase', 'like', "%{$this->search}%"))
            ->when($this->categoryFilter, fn ($q) => $q->where('category', $this->categoryFilter))
            ->orderBy('category')
            ->orderBy('phrase')
            ->paginate(15);

        return view('livewire.admin.filler-words.index', ['fillerWords' => $fillerWords])
            ->layout('layouts.app', ['title' => 'Filler Words']);
    }
}
