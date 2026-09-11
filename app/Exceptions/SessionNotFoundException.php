<?php

namespace App\Exceptions;

use RuntimeException;

class SessionNotFoundException extends RuntimeException
{
    public static function forUser(): self
    {
        return new self('Sesi latihan tidak ditemukan atau sudah tidak aktif.', 404);
    }
}