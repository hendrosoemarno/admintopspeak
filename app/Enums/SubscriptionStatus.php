<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case FREE = 'FREE';
    case PREMIUM_MONTHLY = 'PREMIUM_MONTHLY';
    case PREMIUM_YEARLY = 'PREMIUM_YEARLY';

    public function label(): string
    {
        return match ($this) {
            self::FREE => 'Free Tier',
            self::PREMIUM_MONTHLY => 'Premium Monthly',
            self::PREMIUM_YEARLY => 'Premium Yearly',
        };
    }

    public function isPremium(): bool
    {
        return $this !== self::FREE;
    }
}