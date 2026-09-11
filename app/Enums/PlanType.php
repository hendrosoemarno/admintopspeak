<?php

namespace App\Enums;

enum PlanType: string
{
    case TIME = 'TIME';

    public function label(): string
    {
        return match ($this) {
            self::TIME => 'Time-based (Durasi)',
        };
    }
}