<?php

declare(strict_types=1);

namespace App\Thought;

interface ThoughtfulInterface
{

    /**
     * Determines whether given thought is supported by an thoughtful object.
     *
     * @param ThoughtInterface $thought
     *
     * @return bool
     */
    public function isThoughtSupported(ThoughtInterface $thought): bool;

    /**
     * Returns an array of supported thought objects' class names.
     *
     * @return array
     */
    public static function getSupportedThoughts(): array;

}