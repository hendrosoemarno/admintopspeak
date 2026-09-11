<?php

namespace App\Services\Admin;

use App\Enums\PaymentStatus;
use App\Enums\SubscriptionSource;
use App\Enums\SubscriptionStatus;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Repositories\UserRepository;
use Illuminate\Support\Carbon;

/**
 * Assignment manual langganan oleh admin (hadiah/komplain/transaksi offline).
 * Paket di Master Paket Langganan hanya bertipe TIME: membuat UserSubscription
 * aktif (PAID) + set subscription_status & expires_at user.
 */
class ManualSubscriptionService
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    /**
     * Assign paket ke user secara manual.
     *
     * @param int|null $expiresAt  Carbon atau timestamp kadaluarsa (null = dari durasi paket)
     * @param string|null $note    catatan admin
     */
    public function assignPlan(User $user, SubscriptionPlan $plan, ?Carbon $expiresAt = null, ?string $note = null, ?int $adminId = null): array
    {
        $started = now();

        $expires = $expiresAt;
        if ($expires === null) {
            $expires = $this->durationEnd($started, $plan);
        }

        $status = $this->planToStatus($plan);

        // nonaktifkan langganan aktif lama dahulu (history tetap tersimpan)
        UserSubscription::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        UserSubscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'source' => SubscriptionSource::MANUAL,
            'admin_note' => $note,
            'status' => $status,
            'started_at' => $started,
            'expires_at' => $expires,
            'payment_status' => PaymentStatus::PAID,
            'is_active' => true,
            'amount' => $plan->price_discount ?? $plan->price_original,
        ]);

        $this->userRepository->activatePremium($user, $status, $expires);

        return [
            'type' => 'TIME',
            'status' => $status,
            'expires_at' => $expires,
        ];
    }

    public function changeExpiry(User $user, int $subscriptionId, Carbon $expiresAt, ?string $note = null): UserSubscription
    {
        $subscription = UserSubscription::where('id', $subscriptionId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $subscription->expires_at = $expiresAt;

        if ($note !== null) {
            $subscription->admin_note = $note;
        }

        $subscription->save();

        // Sinkron status premium user bila ini langganan aktifnya
        if ($subscription->is_active && $subscription->status->isPremium()) {
            $this->userRepository->activatePremium($user, $subscription->status, $expiresAt);
        }

        return $subscription;
    }

    /**
     * Tambah/kurangi masa aktif langganan aktif relatif dari expiry saat ini (hari/bulan),
     * tanpa mengubah tanggal mulai.
     *
     * @param string $operation 'add' atau 'subtract'
     * @param string $unit      'DAY' atau 'MONTH'
     */
    public function adjustExpiry(User $user, int $subscriptionId, string $operation, int $value, string $unit, ?string $note = null): UserSubscription
    {
        $subscription = UserSubscription::where('id', $subscriptionId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($subscription->expires_at === null) {
            throw new \InvalidArgumentException('Langganan ini tidak memiliki tanggal kadaluarsa.');
        }

        $current = $subscription->expires_at->copy();

        $newExpiry = $operation === 'subtract'
            ? ($unit === 'MONTH' ? $current->subMonths($value) : $current->subDays($value))
            : ($unit === 'MONTH' ? $current->addMonths($value) : $current->addDays($value));

        return $this->changeExpiry($user, $subscriptionId, $newExpiry, $note);
    }

    public function cancel(User $user, UserSubscription $subscription): void
    {
        if ($user->activeSubscription?->id === $subscription->id) {
            $this->userRepository->expirePremium($user);
        }

        $subscription->is_active = false;
        $subscription->save();
    }

    /**
     * Kembalikan user ke Free Tier: nonaktifkan semua langganan aktif & expire premium.
     */
    public function switchToFreeTier(User $user, ?string $note = null): User
    {
        $data = ['is_active' => false];

        if ($note !== null) {
            $data['admin_note'] = $note;
        }

        UserSubscription::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->update($data);

        return $this->userRepository->expirePremium($user);
    }

    private function durationEnd(Carbon $start, SubscriptionPlan $plan): Carbon
    {
        $value = (int) $plan->duration_value;

        return match (strtoupper((string) $plan->duration_unit)) {
            'DAY' => $start->copy()->addDays($value),
            'YEAR' => $start->copy()->addYears($value),
            default => $start->copy()->addMonths($value),
        };
    }

    private function planToStatus(SubscriptionPlan $plan): SubscriptionStatus
    {
        return strtoupper((string) $plan->duration_unit) === 'YEAR'
            ? SubscriptionStatus::PREMIUM_YEARLY
            : SubscriptionStatus::PREMIUM_MONTHLY;
    }
}