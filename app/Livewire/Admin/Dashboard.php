<?php

namespace App\Livewire\Admin;

use App\Models\AppConfiguration;
use App\Models\ConversationLog;
use App\Models\GrammarRule;
use App\Models\PendingGrammarRule;
use App\Models\QuestionBank;
use App\Models\ThematicTopic;
use App\Models\User;
use App\Models\VocabularyBank;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public array $stats = [];

    public function mount(): void
    {
        $this->stats = [
            ['label' => 'Total Users', 'value' => User::count(), 'route' => 'admin.users.index', 'icon' => '👥'],
            ['label' => 'Question Banks', 'value' => QuestionBank::count(), 'route' => 'admin.question-banks.index', 'icon' => '📝'],
            ['label' => 'Grammar Rules', 'value' => GrammarRule::count(), 'route' => 'admin.grammar-rules.index', 'icon' => '🧩'],
            ['label' => 'Pending Rules', 'value' => PendingGrammarRule::where('status', 'PENDING')->count(), 'route' => 'admin.pending-rules.index', 'icon' => '⏳'],
            ['label' => 'Thematic Topics', 'value' => ThematicTopic::count(), 'route' => 'admin.thematic-topics.index', 'icon' => '🎭'],
            ['label' => 'Vocabulary Words', 'value' => VocabularyBank::count(), 'route' => 'admin.vocabulary.index', 'icon' => '📚'],
            ['label' => 'Conversation Logs', 'value' => ConversationLog::count(), 'route' => 'admin.conversation-logs.index', 'icon' => '💬'],
        ];
    }

    public function render()
    {
        $topUsers = User::withCount('conversationLogs')
            ->orderByDesc('conversation_logs_count')
            ->limit(5)
            ->get();

        $levelDistribution = User::select('current_cefr_level', DB::raw('count(*) as total'))
            ->groupBy('current_cefr_level')
            ->orderBy('current_cefr_level')
            ->get();

        return view('livewire.admin.dashboard', [
            'topUsers' => $topUsers,
            'levelDistribution' => $levelDistribution,
            'config' => AppConfiguration::current(),
        ])->layout('layouts.app', ['title' => 'Dashboard']);
    }
}