<?php

declare(strict_types=1);

namespace App\Security\Exception;

use RuntimeException;
use Throwable;

class SecurityException extends RuntimeException
{
    public function __construct(string $message, int $code = 403, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
