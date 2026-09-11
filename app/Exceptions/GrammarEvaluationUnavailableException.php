<?php

namespace App\Exceptions;

use RuntimeException;

class GrammarEvaluationUnavailableException extends RuntimeException
{
    public static function forUnavailable(): self
    {
        return new self('Penilaian grammar tidak tersedia: LLM tidak dikonfigurasi atau gagal merespons. Coba lagi nanti.');
    }
}
