<?php

namespace App\Livewire\Admin\Subscriptions;

use App\Enums\CefrLevel;
use App\Enums\PaymentStatus;
use App\Enums\PlanStatus;
use App\Enums\PlanType;
use App\Enums\SubscriptionStatus;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\Admin\ManualSubscriptionService;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Admin: Master Paket + Level CEFR + Manajemen Langganan User.
 *
 * Tab "plans"          : CRUD SubscriptionPlan + daftar Level CEFR statis (enum).
 * Tab "subscriptions"  : list user + status langganan; assign manual, ubah expiry, cancel.
 *                       Serta tabel rekaman pembayaran yang sudah ada.
 */
class Index extends Component
{
    use WithPagination;

    public string $activeTab = 'plans';

    // ===== Filter list user (tab subscriptions) =====
    public string $userSearch = '';

    public string $subStatusFilter = '';

    public string $subPlanFilter = '';

    // ===== Filter rekaman pembayaran (tab subscriptions) =====
    public string $search = '';

    public string $paymentFilter = '';

    // ===== Modal form SubscriptionPlan =====
    public bool $showPlanForm = false;

    public ?int $editingPlanId = null;

    public string $plan_name = '';

    public string $plan_description = '';

    public string $plan_features = '';

    public string $plan_price_original = '';

    public string $plan_price_discount = '';

    public string $plan_duration_value = '';

    public string $plan_duration_unit = 'MONTH';

    public string $plan_status = 'ACTIVE';

    public string $plan_badge_promo = '';

    public int $plan_sort_order = 0;

    // ===== Modal assign langganan user =====
    public bool $showAssignModal = false;

    public ?int $assignUserId = null;

    public string $assignPlanId = '';

    public string $assignExpiresAt = '';

    public string $assignNote = '';

    // ===== Modal ubah expiry =====
    public bool $showExpiryModal = false;

    public int $expirySubId = 0;

    public ?int $expiryUserId = null;

    public string $expiryAt = '';

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'paymentFilter']);
    }

    public function clearUserFilters(): void
    {
        $this->reset(['userSearch', 'subStatusFilter', 'subPlanFilter']);
    }

    // ===================== CRUD SUBSCRIPTION PLAN =====================

    public function openCreatePlan(): void
    {
        $this->resetPlanForm();
        $this->showPlanForm = true;
    }

    public function openEditPlan(int $id): void
    {
        $plan = SubscriptionPlan::findOrFail($id);
        $this->resetPlanForm();
        $this->editingPlanId = $plan->id;
        $this->plan_name = $plan->name;
        $this->plan_description = $plan->description ?? '';
        $this->plan_features = is_array($plan->features) ? implode("\n", $plan->features) : '';
        $this->plan_price_original = (string) $plan->price_original;
        $this->plan_price_discount = $plan->price_discount !== null ? (string) $plan->price_discount : '';
        $this->plan_duration_value = (string) ($plan->duration_value ?? '');
        $this->plan_duration_unit = (string) ($plan->duration_unit ?? 'MONTH');
        $this->plan_status = $plan->status->value;
        $this->plan_badge_promo = $plan->badge_promo ?? '';
        $this->plan_sort_order = (int) $plan->sort_order;
        $this->showPlanForm = true;
    }

    public function closePlanForm(): void
    {
        $this->showPlanForm = false;
        $this->resetPlanForm();
    }

    public function savePlan(): void
    {
        $this->validate([
            'plan_name' => 'required|string|max:100',
            'plan_price_original' => 'required|numeric|min:0',
            'plan_price_discount' => 'nullable|numeric|min:0',
            'plan_duration_value' => 'required|integer|min:1|max:3650',
            'plan_duration_unit' => 'required|in:DAY,MONTH,YEAR',
            'plan_status' => 'required|in:ACTIVE,ARCHIVED',
        ]);

        $features = collect(preg_split('/[\r\n,]+/', $this->plan_features))
            ->map(fn ($f) => trim($f))
            ->filter()
            ->values()
            ->all();

        $data = [
            'name' => $this->plan_name,
            'description' => $this->plan_description ?: null,
            'features' => $features,
            'price_original' => $this->plan_price_original,
            'price_discount' => $this->plan_price_discount !== '' ? $this->plan_price_discount : null,
            'type' => PlanType::TIME->value,
            'status' => $this->plan_status,
            'badge_promo' => $this->plan_badge_promo ?: null,
            'sort_order' => $this->plan_sort_order,
            'duration_value' => (int) $this->plan_duration_value,
            'duration_unit' => $this->plan_duration_unit,
            'quota_sessions' => null,
        ];

        if ($this->editingPlanId) {
            SubscriptionPlan::findOrFail($this->editingPlanId)->update($data);
            $this->dispatch('flash', message: 'Paket langganan diperbarui.');
        } else {
            SubscriptionPlan::create($data);
            $this->dispatch('flash', message: 'Paket langganan ditambahkan.');
        }

        $this->closePlanForm();
    }

    public function deletePlan(int $id): void
    {
        SubscriptionPlan::findOrFail($id)->delete();
        $this->dispatch('flash', message: 'Paket langganan dihapus.');
    }

    // ===================== MANAGE USER SUBSCRIPTION (assign/extend/cancel) =====================

    public function openAssign(int $userId): void
    {
        $this->assignUserId = $userId;
        $this->assignPlanId = '';
        $this->assignExpiresAt = '';
        $this->assignNote = '';
        $this->showAssignModal = true;
    }

    public function closeAssign(): void
    {
        $this->showAssignModal = false;
        $this->assignUserId = null;
    }

    public function assignPlan(): void
    {
        $this->validate([
            'assignUserId' => 'required|exists:users,id',
            'assignPlanId' => 'required|exists:subscription_plans,id',
        ]);

        $user = User::findOrFail($this->assignUserId);
        $plan = SubscriptionPlan::findOrFail($this->assignPlanId);

        $expires = null;
        if ($this->assignExpiresAt !== '') {
            $expires = Carbon::parse($this->assignExpiresAt);
        }

        app(ManualSubscriptionService::class)->assignPlan(
            $user,
            $plan,
            $expires,
            $this->assignNote ?: null,
            auth()->id(),
        );

        $this->dispatch('flash', message: 'Langganan manual berhasil di-assign ke '.$user->name.'.');
        $this->closeAssign();
    }

    public function openExpiry(int $subscriptionId): void
    {
        $sub = UserSubscription::findOrFail($subscriptionId);
        $this->expirySubId = $sub->id;
        $this->expiryUserId = $sub->user_id;
        $this->expiryAt = $sub->expires_at?->format('Y-m-d\TH:i') ?? '';
        $this->showExpiryModal = true;
    }

    public function closeExpiry(): void
    {
        $this->showExpiryModal = false;
        $this->expirySubId = 0;
    }

    public function updateExpiry(): void
    {
        $this->validate([
            'expirySubId' => 'required|integer',
            'expiryUserId' => 'required|exists:users,id',
            'expiryAt' => 'required|date',
        ]);

        $user = User::findOrFail($this->expiryUserId);

        app(ManualSubscriptionService::class)->changeExpiry(
            $user,
            $this->expirySubId,
            Carbon::parse($this->expiryAt),
        );

        $this->dispatch('flash', message: 'Tanggal kadaluarsa langganan diperbarui.');
        $this->closeExpiry();
    }

    public function cancelSubscription(int $subscriptionId): void
    {
        $sub = UserSubscription::findOrFail($subscriptionId);
        $user = User::findOrFail($sub->user_id);
        app(ManualSubscriptionService::class)->cancel($user, $sub);
        $this->dispatch('flash', message: 'Langganan dibatalkan/dinonaktifkan.');
    }

    // ===================== COMPUTED =====================

    #[Computed]
    public function durations(): array
    {
        return [
            'DAY' => 'Hari',
            'MONTH' => 'Bulan',
            'YEAR' => 'Tahun',
        ];
    }

    public function userDisplayStatus(User $user): array
    {
        $sub = $user->subscriptions->first();

        if ($user->isPremiumActive() && $sub && $sub->is_active && $sub->status->isPremium()) {
            return ['active', 'Aktif', 'bg-green-100 text-green-700'];
        }

        if ($sub && $sub->payment_status === PaymentStatus::PENDING) {
            return ['pending', 'Pending Payment', 'bg-amber-100 text-amber-700'];
        }

        if ($sub && $sub->status->isPremium() && $sub->expires_at) {
            return $sub->expires_at->isPast()
                ? ['expired', 'Expired', 'bg-red-100 text-red-700']
                : ['canceled', 'Canceled', 'bg-slate-200 text-slate-600'];
        }

        return ['free', 'Free', 'bg-slate-100 text-slate-600'];
    }

    // ===================== RENDER =====================

    public function render()
    {
        $plans = SubscriptionPlan::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $levels = CefrLevel::cases();

        $users = collect();
        $subscriptions = collect();

        if ($this->activeTab === 'subscriptions') {
            $users = User::query()
                ->with(['subscriptions' => fn ($q) => $q->latest(), 'activeSubscription.plan'])
                ->when($this->userSearch, function ($q) {
                    $q->where(function ($inner) {
                        $inner->where('name', 'like', "%{$this->userSearch}%")
                            ->orWhere('email', 'like', "%{$this->userSearch}%")
                            ->orWhere('phone_number', 'like', "%{$this->userSearch}%");
                    });
                })
                ->when($this->subStatusFilter === 'active', function ($q) {
                    $q->where('subscription_status', '!=', SubscriptionStatus::FREE->value)
                        ->where(fn ($inner) => $inner->whereNull('subscription_expires_at')->orWhere('subscription_expires_at', '>', now()))
                        ->whereHas('activeSubscription', fn ($s) => $s->where('is_active', true));
                })
                ->when($this->subStatusFilter === 'free', fn ($q) => $q->where('subscription_status', SubscriptionStatus::FREE->value))
                ->when($this->subStatusFilter === 'pending', function ($q) {
                    $q->whereHas('subscriptions', fn ($s) => $s->where('payment_status', PaymentStatus::PENDING->value));
                })
                ->when($this->subStatusFilter === 'expired', function ($q) {
                    $q->where('subscription_status', '!=', SubscriptionStatus::FREE->value)
                        ->where('subscription_expires_at', '<', now());
                })
                ->when($this->subStatusFilter === 'canceled', function ($q) {
                    $q->where('subscription_status', SubscriptionStatus::FREE->value)
                        ->whereHas('subscriptions', fn ($s) => $s->where('is_active', false));
                })
                ->when($this->subPlanFilter, fn ($q) => $q->whereHas('subscriptions', fn ($s) => $s->where('plan_id', $this->subPlanFilter)))
                ->orderByDesc('id')
                ->paginate(15);

            $subscriptions = UserSubscription::query()
                ->with(['user:id,name,email', 'plan:id,name'])
                ->when($this->search, fn ($q) => $q->where(function ($inner) {
                    $inner->where('merchant_order_id', 'like', "%{$this->search}%")
                        ->orWhere('payment_ref', 'like', "%{$this->search}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$this->search}%")
                            ->orWhere('email', 'like', "%{$this->search}%"));
                }))
                ->when($this->paymentFilter, fn ($q) => $q->where('payment_status', $this->paymentFilter))
                ->latest()
                ->paginate(15);
        }

        return view('livewire.admin.subscriptions.index', compact('plans', 'levels', 'users', 'subscriptions'))
            ->layout('layouts.app', ['title' => 'Subscriptions']);
    }

    private function resetPlanForm(): void
    {
        $this->editingPlanId = null;
        $this->plan_name = '';
        $this->plan_description = '';
        $this->plan_features = '';
        $this->plan_price_original = '';
        $this->plan_price_discount = '';
        $this->plan_duration_value = '';
        $this->plan_duration_unit = 'MONTH';
        $this->plan_status = 'ACTIVE';
        $this->plan_badge_promo = '';
        $this->plan_sort_order = 0;
        $this->resetValidation();
    }

    #[On('flash')]
    public function flash(string $message): void
    {
        session()->flash('status', $message);
    }
}
