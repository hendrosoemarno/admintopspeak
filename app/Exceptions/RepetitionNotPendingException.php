<?php

namespace App\Exceptions;

use RuntimeException;

class RepetitionNotPendingException extends RuntimeException
{
    public static function forTurn(int $turnNumber): self
    {
        return new self("Turn {$turnNumber} tidak sedang menunggu pengulangan (WAITING_REPETITION).", 422);
    }
}