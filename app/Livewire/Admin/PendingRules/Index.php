<?php

namespace App\Livewire\Admin\PendingRules;

use App\Models\GrammarRule;
use App\Models\PendingGrammarRule;
use App\Services\Llm\GrammarRuleSuggestionService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $statusFilter = 'PENDING';

    public ?GrammarRule $detailRule = null;

    public ?string $detailCorrectSentence = null;

    public ?int $previewPendingId = null;

    public ?array $previewSuggestion = null;

    public function viewDetail(int $id): void
    {
        $this->detailRule = GrammarRule::find($id);

        $this->detailCorrectSentence = $this->detailRule
            ? PendingGrammarRule::where('grammar_rule_id', $id)
                ->whereNotNull('suggested_correct_sentence')
                ->latest()
                ->value('suggested_correct_sentence')
            : null;
    }

    public function closeDetail(): void
    {
        $this->detailRule = null;
        $this->detailCorrectSentence = null;
    }

    public function approve(int $id): void
    {
        $pending = PendingGrammarRule::findOrFail($id);

        $suggestion = app(GrammarRuleSuggestionService::class)->suggest(
            userInput: $pending->raw_user_input,
            detectedError: $pending->detected_error,
            suggestedRegex: $pending->suggested_regex,
            isKnownError: $pending->grammar_rule_id !== null,
        );

        if ($suggestion === null) {
            $this->dispatch('flash', message: 'Tidak bisa dibuatkan rule: tidak ada konteks error spesifik pada usulan ini. Periksa kalimat user secara manual.');
            return;
        }

        // Tampilkan pratinjau hasil LLM; rule belum disimpan sampai admin klik "Simpan Rule".
        $this->previewPendingId = $pending->id;
        $this->previewSuggestion = $suggestion;
    }

    public function saveRule(): void
    {
        if ($this->previewPendingId === null || $this->previewSuggestion === null) {
            return;
        }

        $pending = PendingGrammarRule::findOrFail($this->previewPendingId);
        $suggestion = $this->previewSuggestion;

        $ruleCode = 'USER_'.str_pad((string) $pending->id, 3, '0', STR_PAD_LEFT);

        // Jika sebelumnya sudah disetujui (regenerasi), perbarui rule yang ada.
        $rule = $pending->grammarRule ?? GrammarRule::create([
            'rule_code' => $ruleCode,
            'category' => $suggestion['category'],
            'rule_type' => $suggestion['rule_type'],
            'source' => $suggestion['source'],
            'llm_meta' => $suggestion['llm_meta'],
            'cefr_level' => $suggestion['cefr_level'],
            'regex_pattern' => $suggestion['regex_pattern'],
            'description' => $suggestion['description'],
            'is_active' => true,
        ]);

        $rule->update([
            'category' => $suggestion['category'],
            'rule_type' => $suggestion['rule_type'],
            'source' => $suggestion['source'],
            'llm_meta' => $suggestion['llm_meta'],
            'cefr_level' => $suggestion['cefr_level'],
            'regex_pattern' => $suggestion['regex_pattern'],
            'description' => $suggestion['description'],
            'is_active' => true,
        ]);

        $pending->update([
            'status' => 'APPROVED',
            'grammar_rule_id' => $rule->id,
            'suggested_correct_sentence' => $suggestion['correct_sentence'] ?? null,
        ]);

        $label = $suggestion['source'] === 'llm' ? 'LLM' : 'heuristic';
        $type = $suggestion['rule_type'] === 'positive' ? 'pola benar' : 'pola error';

        $this->previewPendingId = null;
        $this->previewSuggestion = null;

        $this->dispatch('flash', message: "Rule disimpan ({$label}, {$type}) — {$rule->rule_code}.");
    }

    public function cancelPreview(): void
    {
        $this->previewPendingId = null;
        $this->previewSuggestion = null;
    }

    public function reject(int $id): void
    {
        $pending = PendingGrammarRule::findOrFail($id);
        $pending->update(['status' => 'REJECTED']);
        $this->dispatch('flash', message: 'Usulan ditolak.');
    }

    public function reopen(int $id): void
    {
        $pending = PendingGrammarRule::findOrFail($id);
        $pending->update(['status' => 'PENDING']);
        $this->dispatch('flash', message: 'Usulan dikembalikan ke status pending.');
    }

    public function llmConfigured(): bool
    {
        return app(\App\Services\Llm\LlmClient::class)->enabled();
    }

    private function escapeToRegex(string $text): string
    {
        return '/'.preg_quote(trim($text), '/').'/i';
    }

    #[On('flash')]
    public function flash(string $message): void
    {
        session()->flash('status', $message);
    }

    public function render()
    {
        $pendingList = PendingGrammarRule::query()
            ->with('grammarRule')
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.pending-rules.index', [
            'pendingList' => $pendingList,
            'llmConfigured' => app(\App\Services\Llm\LlmClient::class)->enabled(),
        ])
            ->layout('layouts.app', ['title' => 'Pending Grammar Rules']);
    }
}
