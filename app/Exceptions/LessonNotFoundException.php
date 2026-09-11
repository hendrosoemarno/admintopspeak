<?php

namespace App\Exceptions;

use RuntimeException;

class LessonNotFoundException extends RuntimeException
{
    public static function forLesson(): self
    {
        return new self('Lesson tidak ditemukan.', 404);
    }
}