<?php

namespace App\Services\Engine;

use App\Enums\SubscriptionStatus;
use App\Models\AppConfiguration;
use App\Models\User;

/**
 * Manajemen kuota Free Tier: nilai sesi awal dapat diubah admin di halaman App Config
 * (disimpan di DB), default 1 sesi. Paywall muncul saat kuota habis dan user FREE.
 */
class QuotaService
{
    public const GUEST_SESSIONS = 1;

    /** Sesi trial awal guest baru, membaca pengaturan dari DB. */
    public static function initialGuestSessions(): int
    {
        return AppConfiguration::initialFreeSessions();
    }

    public function canStartSession(User $user): bool
    {
        return $user->isPremiumActive() || $user->remaining_trial_sessions > 0;
    }

    public function paywallPayload(User $user): array
    {
        return [
            'is_paywalled' => true,
            'remaining_trial_sessions' => $user->remaining_trial_sessions,
            'subscription_status' => SubscriptionStatus::FREE->value,
            'offer' => 'Upgrade ke Premium untuk melanjutkan latihan di level CEFR berapapun tanpa batas.',
        ];
    }

    public function consumeTrialSession(User $user): void
    {
        if ($user->subscription_status->isPremium()) {
            return;
        }

        if ($user->remaining_trial_sessions > 0) {
            $user->decrement('remaining_trial_sessions', 1);
        }
    }
}