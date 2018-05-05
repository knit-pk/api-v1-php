<?php

declare(strict_types=1);

namespace App\EntityBatchProcessor\Handler;

use Throwable;

interface EntityBatchProcessorHandlerInterface
{
    /**
     * Handles entity with specified behaviour.
     *
     * @param object $entity
     */
    public function handle(object $entity): void;

    /**
     * Hook that should be called after handle process.
     *
     * @param object $entity
     */
    public function success(object $entity): void;

    /**
     * Hook used to handle processing error.
     *
     * @param object    $entity
     * @param Throwable $error
     */
    public function error(object $entity, Throwable $error): void;
}
