<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Livewire\Component;

class Show extends Component
{
    public User $user;

    public string $activeTab = 'history';

    public function mount(User $user): void
    {
        $this->user = $user
            ->loadCount(['conversationLogs', 'levelHistories', 'subscriptions'])
            ->load('activeSubscription.plan');
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.admin.users.show', [
            'levelHistories' => $this->user->levelHistories()->latest()->limit(20)->get(),
            'subscriptions' => $this->user->subscriptions()->latest()->limit(20)->get(),
            'conversationLogs' => $this->user->conversationLogs()->with('question')->latest()->limit(50)->get(),
        ])->layout('layouts.app', ['title' => 'Detail Pengguna']);
    }
}