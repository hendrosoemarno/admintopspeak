<?php

namespace App\Enums;

enum LessonDifficulty: string
{
    case EASY = 'Easy';
    case MEDIUM = 'Medium';
    case DIFFICULT = 'Difficult';

    public function label(): string
    {
        return match ($this) {
            self::EASY => 'Easy',
            self::MEDIUM => 'Medium',
            self::DIFFICULT => 'Difficult',
        };
    }
}