<?php

namespace App\Enums;

enum SubscriptionSource: string
{
    case GATEWAY = 'GATEWAY';
    case MANUAL = 'MANUAL';

    public function label(): string
    {
        return match ($this) {
            self::GATEWAY => 'Pembayaran Duitku',
            self::MANUAL => 'Manual (Admin)',
        };
    }

    public function isManual(): bool
    {
        return $this === self::MANUAL;
    }

    public function isGateway(): bool
    {
        return $this === self::GATEWAY;
    }
}
