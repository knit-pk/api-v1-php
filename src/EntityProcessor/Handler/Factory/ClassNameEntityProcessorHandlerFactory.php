<?php

declare(strict_types=1);

namespace App\EntityProcessor\Handler\Factory;

use App\EntityProcessor\Handler\EntityProcessorHandlerInterface;
use Assert\Assertion;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class ClassNameEntityProcessorHandlerFactory implements EntityProcessorHandlerFactoryInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Assert\InvalidArgumentException
     */
    public function make(string $handler, array $options = []): EntityProcessorHandlerInterface
    {
        return $this->assertEntityProcessorHandler(
            $this->container->has($handler)
                ? $this->container->get($handler)
                : $this->makeByConstruct($handler)
        );
    }

    /**
     * @param string $handler
     *
     * @throws \Assert\InvalidArgumentException
     *
     * @return object
     */
    private function makeByConstruct(string $handler): object
    {
        Assertion::classExists($handler, 'Handler class "%s" does not exist');

        return new $handler();
    }

    /**
     * @param object $supposedHandler
     *
     * @throws \InvalidArgumentException
     *
     * @return EntityProcessorHandlerInterface
     */
    private function assertEntityProcessorHandler(object $supposedHandler): EntityProcessorHandlerInterface
    {
        if (!$supposedHandler instanceof EntityProcessorHandlerInterface) {
            throw new InvalidArgumentException(\sprintf('Provided handler %s exists but does not implement EntityProcessorHandlerInterface', \get_class($supposedHandler)));
        }

        return $supposedHandler;
    }
}
