<?php

namespace App\Enums;

enum TestType: string
{
    case ADAPTIVE = 'ADAPTIVE';
    case IELTS_SPEAKING = 'IELTS_SPEAKING';
    case TOEFL_IBT = 'TOEFL_IBT';

    public function label(): string
    {
        return match ($this) {
            self::ADAPTIVE => 'Adaptive',
            self::IELTS_SPEAKING => 'IELTS Speaking',
            self::TOEFL_IBT => 'TOEFL iBT',
        };
    }
}