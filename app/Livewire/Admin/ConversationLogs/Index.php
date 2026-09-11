<?php

namespace App\Livewire\Admin\ConversationLogs;

use App\Enums\SessionStepState;
use App\Models\ConversationLog;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?string $stateFilter = '';

    public ?bool $errorOnly = false;

    public function clearFilters(): void
    {
        $this->reset(['search', 'stateFilter', 'errorOnly']);
    }

    public function render()
    {
        $logs = ConversationLog::query()
            ->with(['user:id,name,email', 'question:id,question_text,cefr_level'])
            ->when($this->search, fn ($q) => $q->where(function ($inner) {
                $inner->where('user_response_text', 'like', "%{$this->search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$this->search}%"))
                    ->orWhere('session_id', 'like', "%{$this->search}%");
            }))
            ->when($this->stateFilter, fn ($q) => $q->where('step_state', $this->stateFilter))
            ->when($this->errorOnly, fn ($q) => $q->where('has_error', true))
            ->latest()
            ->paginate(20);

        return view('livewire.admin.conversation-logs.index', ['logs' => $logs])
            ->layout('layouts.app', ['title' => 'Conversation Logs']);
    }
}