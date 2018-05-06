<?php

declare(strict_types=1);

namespace App\EntityProcessor\Handler\Factory;

use App\EntityProcessor\Handler\EntityProcessorHandlerInterface;

interface EntityProcessorHandlerFactoryInterface
{
    /**
     * @param string $handler
     * @param array  $options
     *
     * @return EntityProcessorHandlerInterface
     */
    public function make(string $handler, array $options = []): EntityProcessorHandlerInterface;
}
