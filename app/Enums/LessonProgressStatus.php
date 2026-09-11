<?php

namespace App\Enums;

enum LessonProgressStatus: string
{
    case NOT_PASSED = 'NOT_PASSED';
    case PASSED = 'PASSED';

    public function label(): string
    {
        return match ($this) {
            self::NOT_PASSED => 'Belum Lulus',
            self::PASSED => 'Lulus',
        };
    }
}