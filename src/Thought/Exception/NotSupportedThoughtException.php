<?php

declare(strict_types=1);

namespace App\Thought\Exception;

use App\Thought\ThoughtfulInterface;
use App\Thought\ThoughtInterface;
use ReflectionClass;
use RuntimeException;

final class NotSupportedThoughtException extends RuntimeException
{
    public function __construct(ThoughtfulInterface $thoughtful, ThoughtInterface $thought, \Throwable $previous = null)
    {
        $thoughtfulRefection = new ReflectionClass($thoughtful);
        $thoughtReflection = new ReflectionClass($thought);

        $message = \sprintf('Object %s does not support thought of class %s. Supported: %s.', $thoughtfulRefection->getShortName(), $thoughtReflection->getShortName(), \implode(', ', $thoughtful::getSupportedThoughts()));
        parent::__construct($message, 500, $previous);
    }
}
