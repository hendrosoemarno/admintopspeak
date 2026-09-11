<?php

namespace App\Enums;

enum CefrLevel: string
{
    case A1 = 'A1';
    case A2 = 'A2';
    case B1 = 'B1';
    case B2 = 'B2';
    case C1 = 'C1';
    case C2 = 'C2';

    public function label(): string
    {
        return match ($this) {
            self::A1 => 'Beginner (A1)',
            self::A2 => 'Elementary (A2)',
            self::B1 => 'Intermediate (B1)',
            self::B2 => 'Upper-Intermediate (B2)',
            self::C1 => 'Advanced (C1)',
            self::C2 => 'Proficiency (C2)',
        };
    }

    public function next(): ?self
    {
        $levels = self::cases();
        $index = array_search($this, $levels, true);

        return $index !== false && isset($levels[$index + 1]) ? $levels[$index + 1] : null;
    }
}
