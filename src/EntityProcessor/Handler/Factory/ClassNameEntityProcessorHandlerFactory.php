<?php

declare(strict_types=1);

namespace App\EntityProcessor\Handler\Factory;

use App\EntityProcessor\Handler\EntityProcessorHandlerInterface;
use InvalidArgumentException;

final class ClassNameEntityProcessorHandlerFactory implements EntityProcessorHandlerFactoryInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function make(string $handler, array $options = []): EntityProcessorHandlerInterface
    {
        if (!\class_exists($handler)) {
            throw new InvalidArgumentException(\sprintf('Handler class %s does not exist', $handler));
        }

        return new $handler();
    }
}
