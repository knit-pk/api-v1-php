<?php

declare(strict_types=1);

namespace App\Bundle\SwooleBundle\DependencyInjection\Compiler;

use App\Bundle\SwooleBundle\Server\Runtime\BootManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class BootManagerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(BootManager::class)) {
            return;
        }

        $definition = $container->findDefinition(BootManager::class);
        $taggedServices = $container->findTaggedServiceIds('swoole.bootable_service');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addService', [new Reference($id)]);
        }
    }
}
