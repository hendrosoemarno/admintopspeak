<?php

namespace App\Livewire\Admin\PaymentTest;

use App\Enums\SubscriptionStatus;
use App\Models\AppConfiguration;
use App\Models\PaymentGatewaySetting;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Repositories\UserSubscriptionRepository;
use App\Services\DuitkuService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class Index extends Component
{
    public string $searchEmail = '';

    public int $selectedUserId = 0;

    /** @var array<int, array{id:int,name:string,price:int,duration:string}> */
    public array $plans = [];

    public int $plan_id = 0;

    /** @var array<int, array{code:string,name:string,image:string,fee:string}> */
    public array $methods = [];

    public string $payment_method = '';

    /** @var array<int|string, mixed> */
    public array $userInfo = [];

    /** @var array<int|string, mixed> */
    public array $checkout = [];

    public string $error = '';

    public string $success = '';

    /** @var array<int|string, mixed> */
    public array $gateway = [];

    public function mount(): void
    {
        $this->syncGatewayStatus();
        $this->loadPlans();
    }

    public function syncGatewayStatus(): void
    {
        $settings = PaymentGatewaySetting::current();

        $this->gateway = [
            'is_enabled' => $settings->effectiveIsEnabled(),
            'sandbox' => $settings->effectiveSandbox(),
            'merchant_code' => $settings->effectiveMerchantCode(),
            'base_url' => $settings->effectiveBaseUrl(),
            'api_key_configured' => $settings->effectiveApiKey() !== '',
        ];
    }

    public function loadPlans(): void
    {
        $this->error = '';

        $this->plans = SubscriptionPlan::query()
            ->where('status', \App\Enums\PlanStatus::ACTIVE->value)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (SubscriptionPlan $plan) => [
                'id' => $plan->id,
                'name' => $plan->name,
                'price' => (int) $plan->effectivePrice(),
                'duration' => $plan->durationLabel(),
            ])
            ->values()
            ->all();

        if ($this->plans === []) {
            $this->error = 'Tidak ada paket ACTIVE di Master Paket Langganan. Buat paket dulu di halaman Subscriptions.';
            $this->plan_id = 0;
            $this->methods = [];
            $this->payment_method = '';

            return;
        }

        $this->plan_id = $this->plan_id > 0 ? $this->plan_id : $this->plans[0]['id'];
        $this->loadPaymentMethods();
    }

    public function updatedPlanId(int $value): void
    {
        $this->plan_id = $value;
        $this->loadPaymentMethods();
    }

    public function loadPaymentMethods(): void
    {
        $this->error = '';

        $plan = SubscriptionPlan::find($this->plan_id);
        if ($plan === null) {
            $this->methods = [];
            $this->payment_method = '';

            return;
        }

        try {
            $this->methods = app(DuitkuService::class)->paymentMethods($plan->effectivePrice());
        } catch (\Throwable $e) {
            $this->methods = [];
            $this->payment_method = '';
            $this->error = 'Gagal memuat kanal pembayaran: '.$e->getMessage();

            return;
        }

        if ($this->methods === []) {
            $this->payment_method = '';
            $this->error = 'Tidak ada kanal pembayaran Duitku yang tersedia untuk paket ini.';

            return;
        }

        $this->payment_method = $this->methods[0]['code'];
    }

    public function findUser(): void
    {
        $this->error = '';
        $this->success = '';

        $user = User::query()
            ->where('email', Str::lower(trim($this->searchEmail)))
            ->first();

        if ($user === null) {
            $this->error = "User dengan email '{$this->searchEmail}' tidak ditemukan.";

            return;
        }

        $this->selectUser($user);
    }

    public function ensureTestUser(): void
    {
        $this->error = '';
        $this->success = '';

        $email = 'test-payment@test.com';

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'User Uji Payment',
                'password' => Hash::make('password123'),
                'device_uuid' => (string) Str::uuid(),
                'current_cefr_level' => 'A1',
                'remaining_trial_sessions' => AppConfiguration::initialFreeSessions(),
                'subscription_status' => SubscriptionStatus::FREE,
            ]
        );

        // Pastikan password selalu deterministik agar bisa login di aplikasi Android.
        $user->update(['password' => Hash::make('password123')]);

        $this->selectUser($user);
        $this->success = "User uji siap — email: {$email}, password: password123";
    }

    private function selectUser(User $user): void
    {
        $this->selectedUserId = $user->id;
        $this->searchEmail = $user->email;
        $this->loadUserInfo($user);
    }

    private function loadUserInfo(User $user): void
    {
        $this->userInfo = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_guest' => $user->isGuest(),
            'device_uuid' => $user->device_uuid,
            'subscription_status' => $user->subscription_status->value,
            'is_premium' => $user->isPremiumActive(),
            'subscription_expires_at' => $user->subscription_expires_at?->toIso8601String(),
            'remaining_trial_sessions' => $user->remaining_trial_sessions,
        ];
    }

    public function refreshUserStatus(): void
    {
        $this->error = '';
        $this->success = '';

        $user = $this->currentUser();
        if ($user === null) {
            $this->error = 'Pilih user terlebih dahulu.';

            return;
        }

        $this->loadUserInfo($user);
        $this->success = 'Status user diperbarui.';
    }

    public function createCheckout(): void
    {
        $this->error = '';
        $this->success = '';

        $settings = PaymentGatewaySetting::current();
        if (! $settings->effectiveIsEnabled()) {
            $this->error = 'Payment gateway sedang nonaktif. Aktifkan dulu di halaman Payment Gateway.';

            return;
        }

        $user = $this->currentUser();
        if ($user === null) {
            $this->error = 'Pilih user terlebih dahulu.';

            return;
        }

        $plan = SubscriptionPlan::find($this->plan_id);
        if ($plan === null) {
            $this->error = 'Pilih paket terlebih dahulu.';

            return;
        }

        if ($this->payment_method === '') {
            $this->error = 'Pilih kanal pembayaran terlebih dahulu.';

            return;
        }

        try {
            $payload = app(DuitkuService::class)->purchase(
                user: $user,
                plan: $plan,
                paymentMethod: $this->payment_method,
            );
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();

            return;
        }

        $this->checkout = $payload;
        $this->loadUserInfo($user->fresh());
        $this->success = 'Checkout Duitku berhasil dibuat. Lanjutkan ke halaman pembayaran.';
    }

    public function simulateSuccessCallback(): void
    {
        $this->error = '';
        $this->success = '';

        $merchantOrderId = (string) ($this->checkout['merchant_order_id'] ?? '');
        $amount = (int) ($this->checkout['amount'] ?? 0);

        if ($merchantOrderId === '') {
            $this->error = 'Tidak ada checkout untuk disimulasikan. Buat checkout dulu.';

            return;
        }

        $settings = PaymentGatewaySetting::current();
        $params = [
            'merchantCode' => $settings->effectiveMerchantCode(),
            'merchantOrderId' => $merchantOrderId,
            'amount' => $amount,
            'resultCode' => '00',
            'reference' => 'TEST-'.strtoupper(Str::random(8)),
            'paymentCode' => $this->checkout['payment_method'] ?? 'VC',
            'signature' => hash_hmac('sha256',
                $settings->effectiveMerchantCode().$amount.$merchantOrderId,
                $settings->effectiveApiKey(),
            ),
        ];

        try {
            $result = app(DuitkuService::class)->processCallback($params);
        } catch (\Throwable $e) {
            $this->error = 'Simulasi callback gagal: '.$e->getMessage();

            return;
        }

        if (! ($result['activated'] ?? false)) {
            $this->error = 'Simulasi callback tidak mengaktifkan premium.';

            return;
        }

        $this->syncPaymentStatus();
        $user = $this->currentUser();
        if ($user !== null) {
            $this->loadUserInfo($user->fresh());
        }

        $this->success = 'Callback sukses diproses — premium aktif'
            .($this->userInfo['subscription_expires_at'] ?? null !== null ? ' hingga '.$this->userInfo['subscription_expires_at'] : '').'.';
    }

    public function syncPaymentStatus(): void
    {
        $merchantOrderId = (string) ($this->checkout['merchant_order_id'] ?? '');
        if ($merchantOrderId === '') {
            return;
        }

        $subscription = app(UserSubscriptionRepository::class)->findByMerchantOrderId($merchantOrderId);
        if ($subscription !== null) {
            $this->checkout['payment_status'] = $subscription->payment_status->value;
            $this->checkout['expires_at'] = $subscription->expires_at?->toIso8601String();
        }
    }

    private function currentUser(): ?User
    {
        return $this->selectedUserId > 0 ? User::find($this->selectedUserId) : null;
    }

    #[On('flash')]
    public function flash(string $message): void
    {
        session()->flash('status', $message);
    }

    public function render()
    {
        return view('livewire.admin.payment-test.index')
            ->layout('layouts.app', ['title' => 'Payment Test']);
    }
}