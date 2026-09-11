<?php

namespace App\Exceptions;

use RuntimeException;

class PaywallRequiredException extends RuntimeException
{
    public static function forUser(): self
    {
        return new self('Free quota habis. Silakan upgrade ke Premium untuk melanjutkan latihan.', 402);
    }
}