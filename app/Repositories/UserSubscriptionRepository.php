<?php

namespace App\Repositories;

use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Models\UserSubscription;
use Illuminate\Support\Carbon;

class UserSubscriptionRepository extends Repository
{
    protected function model(): string
    {
        return UserSubscription::class;
    }

    public function latestActiveFor(int $userId): ?UserSubscription
    {
        return $this->query()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->latest()
            ->first();
    }

    public function findByMerchantOrderId(string $merchantOrderId): ?UserSubscription
    {
        return $this->query()->where('merchant_order_id', $merchantOrderId)->first();
    }

    /**
     * Menyiapkan transaksi pembelian baru; menonaktifkan langganan lama yang aktif.
     */
    public function createForPurchase(
        int $userId,
        int $planId,
        SubscriptionStatus $status,
        string $merchantOrderId,
        float $amount,
        string $checkoutUrl,
        string $paymentRef,
        ?string $paymentMethod = null,
    ): UserSubscription {
        return $this->query()->create([
            'user_id' => $userId,
            'plan_id' => $planId,
            'status' => $status->value,
            'payment_provider' => 'duitku',
            'payment_ref' => $paymentRef,
            'merchant_order_id' => $merchantOrderId,
            'amount' => $amount,
            'checkout_url' => $checkoutUrl,
            'payment_method' => $paymentMethod,
            'payment_status' => PaymentStatus::PENDING,
            'is_active' => false,
        ]);
    }

    public function markPaid(UserSubscription $subscription, string $method, ?Carbon $expiresAt): UserSubscription
    {
        $subscription->update([
            'payment_status' => PaymentStatus::PAID,
            'payment_method' => $method ?: $subscription->payment_method,
            'is_active' => true,
            'started_at' => now(),
            'expires_at' => $expiresAt,
        ]);

        return $subscription->fresh();
    }

    public function markExpired(UserSubscription $subscription): void
    {
        $subscription->update(['payment_status' => PaymentStatus::EXPIRED, 'is_active' => false]);
    }

    public function deactivateActiveFor(int $userId): void
    {
        $this->query()->where('user_id', $userId)->where('is_active', true)->update(['is_active' => false]);
    }
}