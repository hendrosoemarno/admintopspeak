<?php

namespace App\Enums;

enum SessionStepState: string
{
    case NORMAL = 'NORMAL';
    case WAITING_REPETITION = 'WAITING_REPETITION';

    public function label(): string
    {
        return match ($this) {
            self::NORMAL => 'Normal',
            self::WAITING_REPETITION => 'Waiting Repetition',
        };
    }
}