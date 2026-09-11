<?php

namespace App\Exceptions;

use RuntimeException;

class AssessmentEvaluationUnavailableException extends RuntimeException
{
    public static function forUnavailable(): self
    {
        return new self('Evaluasi IELTS/TOEFL tidak tersedia: LLM tidak dikonfigurasi atau gagal merespons. Coba lagi nanti.');
    }
}
