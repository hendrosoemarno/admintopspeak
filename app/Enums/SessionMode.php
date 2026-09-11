<?php

namespace App\Enums;

enum SessionMode: string
{
    case ADAPTIVE = 'ADAPTIVE';
    case THEMATIC = 'THEMATIC';
    case IELTS_SPEAKING = 'IELTS_SPEAKING';
    case TOEFL_IBT = 'TOEFL_IBT';

    public function label(): string
    {
        return match ($this) {
            self::ADAPTIVE => 'Adaptive',
            self::THEMATIC => 'Thematic',
            self::IELTS_SPEAKING => 'IELTS Speaking',
            self::TOEFL_IBT => 'TOEFL iBT',
        };
    }

    public function isExam(): bool
    {
        return in_array($this, [self::IELTS_SPEAKING, self::TOEFL_IBT], true);
    }

    /** Test type terkait untuk mode ujian (untuk pemilihan soal). */
    public function testType(): ?string
    {
        return match ($this) {
            self::IELTS_SPEAKING => 'IELTS_SPEAKING',
            self::TOEFL_IBT => 'TOEFL_IBT',
            default => null,
        };
    }
}