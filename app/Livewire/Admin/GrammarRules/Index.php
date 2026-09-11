<?php

namespace App\Livewire\Admin\GrammarRules;

use App\Enums\CefrLevel;
use App\Models\GrammarRule;
use App\Models\PendingGrammarRule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $rule_code = '';

    public string $category = '';

    public string $rule_type = 'error';

    public string $cefr_level = 'A1';

    public string $regex_pattern = '';

    public string $description = '';

    public bool $is_active = true;

    public string $search = '';

    protected function rules(): array
    {
        return [
            'rule_code' => 'required|string|max:50|unique:grammar_rules,rule_code,' . ($this->editingId ?? 'NULL'),
            'category' => 'required|string|max:100',
            'rule_type' => 'required|in:error,positive',
            'cefr_level' => 'required|in:' . implode(',', array_map(fn ($l) => $l->value, CefrLevel::cases())),
            'regex_pattern' => 'required|string',
            'description' => 'required|string',
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
        $rule = GrammarRule::findOrFail($id);
        $this->editingId = $rule->id;
        $this->rule_code = $rule->rule_code;
        $this->category = $rule->category;
        $this->rule_type = $rule->rule_type;
        $this->cefr_level = $rule->cefr_level->value;
        $this->regex_pattern = $rule->regex_pattern;
        $this->description = $rule->description;
        $this->is_active = $rule->is_active;
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
            'rule_code' => strtoupper($this->rule_code),
            'category' => $this->category,
            'rule_type' => $this->rule_type,
            'cefr_level' => $this->cefr_level,
            'regex_pattern' => $this->regex_pattern,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            GrammarRule::findOrFail($this->editingId)->update($data);
            $this->dispatch('flash', message: 'Aturan grammar diperbarui.');
        } else {
            GrammarRule::create($data);
            $this->dispatch('flash', message: 'Aturan grammar ditambahkan.');
        }

        $this->closeForm();
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        GrammarRule::findOrFail($id)->delete();
        $this->dispatch('flash', message: 'Aturan dihapus.');
    }

    public function toggleActive(int $id): void
    {
        $rule = GrammarRule::findOrFail($id);
        $rule->update(['is_active' => ! $rule->is_active]);
    }

    #[Computed]
    public function levels(): array
    {
        return CefrLevel::cases();
    }

    #[Computed]
    public function placeholders(): array
    {
        return app(\App\Services\Grammar\GrammarRulePatternResolver::class)->supportedPlaceholders();
    }

    #[Computed]
    public function pendingCount(): int
    {
        return PendingGrammarRule::where('status', 'PENDING')->count();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->rule_code = '';
        $this->category = '';
        $this->rule_type = 'error';
        $this->cefr_level = 'A1';
        $this->regex_pattern = '';
        $this->description = '';
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
        $rules = GrammarRule::query()
            ->when($this->search, fn ($q) => $q->where(function ($inner) {
                $inner->where('rule_code', 'like', "%{$this->search}%")
                    ->orWhere('category', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%");
            }))
            ->withCount('pendingRules')
            ->orderBy('cefr_level')
            ->orderBy('rule_code')
            ->paginate(15);

        return view('livewire.admin.grammar-rules.index', ['rules' => $rules])
            ->layout('layouts.app', ['title' => 'Grammar Rules']);
    }
}