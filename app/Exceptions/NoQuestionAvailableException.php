<?php

namespace App\Exceptions;

use RuntimeException;

class NoQuestionAvailableException extends RuntimeException
{
    public static function forLevel(string $level): self
    {
        return new self("Belum ada soal tersedia untuk level {$level}.", 404);
    }
}