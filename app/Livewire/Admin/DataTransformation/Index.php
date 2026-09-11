<?php

namespace App\Livewire\Admin\DataTransformation;

use App\Enums\CefrLevel;
use App\Repositories\GrammarDataRepository;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Admin: kelola data Word Transformation Engine.
 * Baca/tulis file JSON di resources/data/grammar/ dengan 4 tab:
 * Verbs, Nouns, Adjectives, Pronouns (+ Demonstratives di tab Pronouns).
 */
class Index extends Component
{
    public string $activeTab = 'verbs';

    public bool $showForm = false;

    public ?int $editingIndex = null;

    public string $formCollection = 'verbs';

    public array $form = [];

    public array $collections = [
        'verbs' => [
            'label' => 'Verbs',
            'file' => 'irregular_verbs.json',
            'rootKey' => null,
            'fields' => [
                ['name' => 'base_v1', 'label' => 'Base (V1)', 'placeholder' => 'go'],
                ['name' => 'past_simple_v2', 'label' => 'Past Simple (V2)', 'placeholder' => 'went'],
                ['name' => 'past_participle_v3', 'label' => 'Past Participle (V3)', 'placeholder' => 'gone'],
                ['name' => 'cefr_level', 'label' => 'CEFR Level', 'type' => 'select'],
            ],
        ],
        'nouns' => [
            'label' => 'Nouns',
            'file' => 'irregular_nouns.json',
            'rootKey' => null,
            'fields' => [
                ['name' => 'singular', 'label' => 'Singular', 'placeholder' => 'child'],
                ['name' => 'plural', 'label' => 'Plural', 'placeholder' => 'children'],
                ['name' => 'cefr_level', 'label' => 'CEFR Level', 'type' => 'select'],
            ],
        ],
        'adjectives' => [
            'label' => 'Adjectives',
            'file' => 'irregular_adjectives.json',
            'rootKey' => null,
            'fields' => [
                ['name' => 'base', 'label' => 'Base', 'placeholder' => 'good'],
                ['name' => 'comparative', 'label' => 'Comparative', 'placeholder' => 'better'],
                ['name' => 'superlative', 'label' => 'Superlative', 'placeholder' => 'best'],
                ['name' => 'derived_adverb', 'label' => 'Derived Adverb', 'placeholder' => 'well'],
                ['name' => 'cefr_level', 'label' => 'CEFR Level', 'type' => 'select'],
            ],
        ],
        'pronouns' => [
            'label' => 'Pronouns',
            'file' => 'demonstratives_and_pronouns.json',
            'rootKey' => 'pronouns',
            'fields' => [
                ['name' => 'subject', 'label' => 'Subject', 'placeholder' => 'he'],
                ['name' => 'object', 'label' => 'Object', 'placeholder' => 'him'],
                ['name' => 'possessive_adjective', 'label' => 'Possessive Adjective', 'placeholder' => 'his'],
                ['name' => 'possessive_pronoun', 'label' => 'Possessive Pronoun', 'placeholder' => 'his'],
                ['name' => 'reflexive', 'label' => 'Reflexive', 'placeholder' => 'himself'],
            ],
        ],
        'demonstratives' => [
            'label' => 'Demonstratives',
            'file' => 'demonstratives_and_pronouns.json',
            'rootKey' => 'demonstratives',
            'fields' => [
                ['name' => 'singular', 'label' => 'Singular', 'placeholder' => 'this'],
                ['name' => 'plural', 'label' => 'Plural', 'placeholder' => 'these'],
                ['name' => 'distance', 'label' => 'Distance', 'placeholder' => 'near'],
            ],
        ],
    ];

    public function __construct()
    {
    }

    public function boot(): void
    {
        $this->repo = app(GrammarDataRepository::class);
    }

    private GrammarDataRepository $repo;

    public function setTab(string $tab): void
    {
        $this->activeTab = in_array($tab, array_keys($this->collections), true) ? $tab : 'verbs';
        $this->showForm = false;
        $this->closeForm();
    }

    #[Computed]
    public function tabs(): array
    {
        return [
            'verbs' => ['label' => 'Verbs', 'icon' => '⚙️'],
            'nouns' => ['label' => 'Nouns', 'icon' => '📦'],
            'adjectives' => ['label' => 'Adjectives', 'icon' => '🎨'],
            'pronouns' => ['label' => 'Pronouns', 'icon' => '👤'],
        ];
    }

    public function items(string $collection = null): array
    {
        $config = $this->collections[$collection ?? $this->activeTab];

        if ($config['rootKey'] === null) {
            return $this->repo->read($config['file']);
        }

        $file = $this->repo->read($config['file']);

        return $file[$config['rootKey']] ?? [];
    }

    public function openCreate(string $collection): void
    {
        $this->resetForm();
        $this->formCollection = $collection;
        $this->showForm = true;
        $this->editingIndex = null;
    }

    public function openEdit(int $index, string $collection): void
    {
        $config = $this->collections[$collection];
        $item = $this->items($collection)[$index] ?? null;

        if ($item === null) {
            return;
        }

        $this->formCollection = $collection;
        $this->editingIndex = $index;
        $this->form = array_fill_keys(array_column($config['fields'], 'name'), '');
        foreach ($config['fields'] as $field) {
            $this->form[$field['name']] = $item[$field['name']] ?? '';
        }
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $config = $this->collections[$this->formCollection];
        $this->validate($this->rulesFor($this->formCollection));

        $file = $this->repo->read($config['file']);
        $list = $config['rootKey'] ? ($file[$config['rootKey']] ?? []) : $file;

        $item = [];
        foreach ($config['fields'] as $field) {
            $item[$field['name']] = $this->normalize($field, $this->form[$field['name']] ?? '');
        }

        if ($this->editingIndex !== null) {
            $list[$this->editingIndex] = $item;
        } else {
            $list[] = $item;
        }

        if ($config['rootKey']) {
            $file[$config['rootKey']] = array_values($list);
        } else {
            $file = array_values($list);
        }

        $this->repo->write($config['file'], $file);

        $this->dispatch('flash', message: ($this->editingIndex !== null ? 'Diperbarui' : 'Ditambahkan')." - {$config['label']}.");
        $this->closeForm();
    }

    public function delete(int $index, string $collection): void
    {
        $config = $this->collections[$collection];
        $file = $this->repo->read($config['file']);
        $list = $config['rootKey'] ? ($file[$config['rootKey']] ?? []) : $file;

        unset($list[$index]);

        if ($config['rootKey']) {
            $file[$config['rootKey']] = array_values($list);
        } else {
            $file = array_values($list);
        }

        $this->repo->write($config['file'], $file);

        $this->dispatch('flash', message: "Dihapus - {$config['label']}.");
    }

    #[Computed]
    public function levels(): array
    {
        return array_map(fn ($l) => $l->value, CefrLevel::cases());
    }

    private function rulesFor(string $collection): array
    {
        $rules = [];

        foreach ($this->collections[$collection]['fields'] as $field) {
            $key = 'form.'.$field['name'];

            $rules[$key] = ($field['type'] ?? 'text') === 'select'
                ? ['required', 'in:'.implode(',', $this->levels)]
                : ['required', 'string', 'max:100'];
        }

        return $rules;
    }

    private function normalize(array $field, string $value): string
    {
        if (($field['type'] ?? 'text') === 'select') {
            return strtoupper(trim($value));
        }

        return strtolower(trim($value));
    }

    private function resetForm(): void
    {
        $this->editingIndex = null;
        $this->form = [];
        $this->formCollection = $this->activeTab === 'pronouns' ? 'pronouns' : $this->activeTab;
        $this->resetValidation();
    }

    #[On('flash')]
    public function flash(string $message): void
    {
        session()->flash('status', $message);
    }

    public function render()
    {
        return view('livewire.admin.data-transformation.index')
            ->layout('layouts.app', ['title' => 'Data Transformation']);
    }
}
