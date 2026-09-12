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

    public function deleteUser()
    {
        if ($this->user->id === auth()->id()) {
            session()->flash('status', 'Tidak bisa menghapus akun yang sedang login.');

            return redirect()->route('admin.users.show', $this->user);
        }

        if ($this->user->is_admin) {
            session()->flash('status', 'Tidak bisa menghapus akun admin.');

            return redirect()->route('admin.users.show', $this->user);
        }

        $this->user->tokens()->delete();
        $this->user->delete();

        return redirect()->route('admin.users.index');
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