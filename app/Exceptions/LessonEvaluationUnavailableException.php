<?php

namespace App\Exceptions;

use RuntimeException;

class LessonEvaluationUnavailableException extends RuntimeException
{
    public static function forUnavailable(): self
    {
        return new self('Mesin evaluasi AI sedang tidak tersedia atau belum dikonfigurasi. Silakan coba lagi.', 422);
    }
}