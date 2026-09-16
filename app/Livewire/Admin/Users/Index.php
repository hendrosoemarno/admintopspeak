<?php

namespace App\Livewire\Admin\Users;

use App\Enums\CefrLevel;
use App\Enums\SubscriptionStatus;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\Admin\ManualSubscriptionService;
use App\Services\Admin\SessionQuotaService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?string $levelFilter = '';

    public ?string $subscriptionFilter = '';

    public string $sortBy = 'id';

    public string $sortDir = 'desc';

    public ?int $editingId = null;

    public int $remainingSessions = 0;

    public ?int $editingLevelId = null;

    public string $levelField = '';

    public ?int $editingSubId = null;

    public string $subPlanId = '';

    public string $subNote = '';

    public bool $showQuotaModal = false;

    public ?int $quotaUserId = null;

    public string $quotaOperation = 'add';

    public int $quotaValue = 1;

    public string $quotaReason = '';

    public bool $showLogModal = false;

    public ?int $logUserId = null;

    public bool $showDurationModal = false;

    public ?int $durationSubId = null;

    public ?int $durationUserId = null;

    public string $durationOperation = 'add';

    public int $durationValue = 1;

    public string $durationUnit = 'DAY';

    public string $durationNote = '';

    public bool $showDeleteModal = false;

    public ?int $deleteUserId = null;

    public function queryString(): array
    {
        return [
            'search' => ['except' => ''],
            'levelFilter' => ['except' => ''],
            'subscriptionFilter' => ['except' => ''],
        ];
    }

    public function startEdit(int $id): void
    {
        $user = User::findOrFail($id);
        $this->editingId = $user->id;
        $this->remainingSessions = $user->remaining_trial_sessions;
    }

    public function saveQuota(): void
    {
        $this->validate(['remainingSessions' => 'required|integer|min:0|max:100']);

        $user = User::findOrFail($this->editingId);
        $user->remaining_trial_sessions = $this->remainingSessions;
        $user->save();
        $user->syncTotalFreeSessionsGranted();

        $this->reset(['editingId', 'remainingSessions']);
    }

    public function openQuotaModal(int $userId): void
    {
        $user = User::findOrFail($userId);
        $this->quotaUserId = $user->id;
        $this->quotaOperation = 'add';
        $this->quotaValue = 1;
        $this->quotaReason = '';
        $this->showQuotaModal = true;
    }

    public function closeQuotaModal(): void
    {
        $this->showQuotaModal = false;
        $this->quotaUserId = null;
    }

    public function applyQuota(): void
    {
        $this->validate([
            'quotaUserId' => 'required|exists:users,id',
            'quotaOperation' => 'required|in:add,subtract,set',
            'quotaValue' => 'required|integer|min:0|max:9999',
            'quotaReason' => 'required|string|max:255',
        ]);

        $user = User::findOrFail($this->quotaUserId);

        $service = app(SessionQuotaService::class);

        match ($this->quotaOperation) {
            'add' => $service->add($user->id, $this->quotaValue, $this->quotaReason, auth()->id()),
            'subtract' => $service->add($user->id, -$this->quotaValue, $this->quotaReason, auth()->id()),
            'set' => $service->set($user->id, $this->quotaValue, $this->quotaReason, auth()->id()),
        };

        $this->dispatch('flash', message: "Sisa sesi {$user->name} diperbarui.");
        $this->closeQuotaModal();
    }

    public function openLogModal(int $userId): void
    {
        $this->logUserId = $userId;
        $this->showLogModal = true;
    }

    public function closeLogModal(): void
    {
        $this->showLogModal = false;
        $this->logUserId = null;
    }

    public function openDurationModal(int $subscriptionId): void
    {
        $sub = UserSubscription::findOrFail($subscriptionId);

        $this->durationSubId = $sub->id;
        $this->durationUserId = $sub->user_id;
        $this->durationOperation = 'add';
        $this->durationValue = 1;
        $this->durationUnit = 'DAY';
        $this->durationNote = '';
        $this->showDurationModal = true;
    }

    public function closeDurationModal(): void
    {
        $this->showDurationModal = false;
        $this->reset(['durationSubId', 'durationUserId']);
    }

    public function adjustDuration(): void
    {
        $this->validate([
            'durationSubId' => 'required|integer',
            'durationUserId' => 'required|exists:users,id',
            'durationOperation' => 'required|in:add,subtract',
            'durationValue' => 'required|integer|min:1|max:3650',
            'durationUnit' => 'required|in:DAY,MONTH',
        ]);

        $user = User::findOrFail($this->durationUserId);

        app(ManualSubscriptionService::class)->adjustExpiry(
            $user,
            $this->durationSubId,
            $this->durationOperation,
            $this->durationValue,
            $this->durationUnit,
            $this->durationNote ?: null,
        );

        $this->dispatch('flash', message: "Sisa masa aktif {$user->name} diperbarui.");
        $this->closeDurationModal();
    }

    public function openDeleteModal(int $userId): void
    {
        $user = User::withCount(['conversationLogs', 'levelHistories', 'subscriptions'])
            ->findOrFail($userId);

        $this->deleteUserId = $user->id;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteUserId = null;
    }

    #[Computed]
    public function deletingUser(): ?User
    {
        if (! $this->deleteUserId) {
            return null;
        }

        return User::withCount(['conversationLogs', 'levelHistories', 'subscriptions'])
            ->with('activeSubscription.plan')
            ->find($this->deleteUserId);
    }

    public function deleteUser(): void
    {
        $user = User::withCount(['conversationLogs', 'levelHistories', 'subscriptions'])
            ->findOrFail($this->deleteUserId);

        if ($user->id === auth()->id()) {
            $this->dispatch('flash', message: 'Tidak bisa menghapus akun yang sedang login.');
            $this->closeDeleteModal();

            return;
        }

        if ($user->is_admin) {
            $this->dispatch('flash', message: 'Tidak bisa menghapus akun admin.');
            $this->closeDeleteModal();

            return;
        }

        // Tokens Sanctum memakai relasi polymorphic (tanpa FK), hapus manual.
        $user->tokens()->delete();
        $user->delete();

        $this->dispatch('flash', message: "User {$user->name} beserta seluruh datanya dihapus.");
        $this->closeDeleteModal();
        $this->resetPage();
    }

    public function quotaLogs(int $userId): Collection
    {
        return \App\Models\UserSessionQuotaLog::query()
            ->with('admin:id,name')
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    #[On('flash')]
    public function flash(string $message): void
    {
        session()->flash('status', $message);
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'remainingSessions']);
    }

    public function startEditLevel(int $id): void
    {
        $user = User::findOrFail($id);
        $this->editingLevelId = $user->id;
        $this->levelField = $user->current_cefr_level->value;
    }

    public function saveLevel(): void
    {
        $this->validate([
            'editingLevelId' => 'required|integer',
            'levelField' => 'required|string|in:'.collect(CefrLevel::cases())->pluck('value')->implode(','),
        ]);

        $user = User::findOrFail($this->editingLevelId);

        app(\App\Repositories\UserRepository::class)->setUserLevel($user, $this->levelField, 'Diubah manual oleh admin', auth()->id());

        $this->dispatch('flash', message: "Level {$user->name} diubah menjadi {$this->levelField}.");
        $this->reset(['editingLevelId', 'levelField']);
    }

    public function cancelEditLevel(): void
    {
        $this->reset(['editingLevelId', 'levelField']);
    }

    public function startEditSub(int $id): void
    {
        $this->editingSubId = $id;
        $this->subPlanId = '';
        $this->subNote = '';
    }

    public function saveSub(): void
    {
        $this->validate([
            'editingSubId' => 'required|exists:users,id',
            'subPlanId' => 'required|string',
        ]);

        $user = User::findOrFail($this->editingSubId);

        if ($this->subPlanId === 'free') {
            app(ManualSubscriptionService::class)->switchToFreeTier($user, $this->subNote ?: null);

            $this->dispatch('flash', message: "Langganan {$user->name} diubah ke Free Tier.");
            $this->reset(['editingSubId', 'subPlanId', 'subNote']);

            return;
        }

        $plan = SubscriptionPlan::findOrFail($this->subPlanId);

        app(ManualSubscriptionService::class)->assignPlan(
            $user,
            $plan,
            null,
            $this->subNote ?: null,
            auth()->id(),
        );

        $this->dispatch('flash', message: "Langganan {$user->name} diubah ke paket {$plan->name}.");
        $this->reset(['editingSubId', 'subPlanId', 'subNote']);
    }

    public function cancelEditSub(): void
    {
        $this->reset(['editingSubId', 'subPlanId', 'subNote']);
    }

    public function sortUsers(string $column): void
    {
        $sortable = [
            'name', 'current_cefr_level', 'subscription_status',
            'remaining_trial_sessions', 'level_histories_count', 'conversation_logs_count',
        ];

        if (! in_array($column, $sortable, true)) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = in_array($column, ['level_histories_count', 'conversation_logs_count'], true)
                ? 'desc'
                : 'asc';
        }

        $this->resetPage();
    }

    #[Computed]
    public function availablePlans(): \Illuminate\Database\Eloquent\Collection
    {
        return SubscriptionPlan::query()
            ->where('status', \App\Enums\PlanStatus::ACTIVE->value)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    #[Computed]
    public function levels(): array
    {
        return CefrLevel::cases();
    }

    #[Computed]
    public function subscriptions(): array
    {
        return SubscriptionStatus::cases();
    }

    #[Computed]
    public function durationSub(): ?UserSubscription
    {
        return $this->durationSubId ? UserSubscription::find($this->durationSubId) : null;
    }

    public function render()
    {
        $users = User::query()
            ->withCount(['conversationLogs', 'levelHistories', 'subscriptions'])
            ->with(['activeSubscription.plan'])
            ->when($this->search, function ($q) {
                $q->where(function ($inner) {
                    $inner->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('phone_number', 'like', "%{$this->search}%")
                        ->orWhere('device_uuid', 'like', "%{$this->search}%");
                });
            })
            ->when($this->levelFilter, fn ($q) => $q->where('current_cefr_level', $this->levelFilter))
            ->when($this->subscriptionFilter, fn ($q) => $q->where('subscription_status', $this->subscriptionFilter))
            ->orderBy($this->sortBy, $this->sortDir)
            ->orderByDesc('id')
            ->paginate(15);

        return view('livewire.admin.users.index', ['users' => $users])
            ->layout('layouts.app', ['title' => 'Users']);
    }
}