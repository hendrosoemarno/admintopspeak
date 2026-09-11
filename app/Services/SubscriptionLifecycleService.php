<?php

namespace App\Services;

use App\Enums\SubscriptionStatus;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Repositories\UserSubscriptionRepository;

/**
 * Siklus hidup langganan: ketika subscription_expires_at terlewati,
 * user premium diturunkan kembali ke FREE dan langganan dinonaktifkan.
 */
class SubscriptionLifecycleService
{
    public function __construct(
        private readonly UserSubscriptionRepository $subscriptions,
        private readonly UserRepository $users,
    ) {
    }

    public function expireOverdueSubscriptions(): int
    {
        $overdue = User::query()
            ->where('subscription_status', '!=', SubscriptionStatus::FREE->value)
            ->whereNotNull('subscription_expires_at')
            ->where('subscription_expires_at', '<', now())
            ->get();

        foreach ($overdue as $user) {
            $this->subscriptions->deactivateActiveFor($user->id);
            $this->users->expirePremium($user);
        }

        return $overdue->count();
    }
}