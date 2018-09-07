<?php

declare(strict_types=1);

namespace App\Bundle\SwooleBundle\Bridge\Symfony\HttpKernel;

use App\Bundle\SwooleBundle\Functions\ServerUtils;
use App\Bundle\SwooleBundle\Server\RequestHandlerInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

final class DebugHttpKernelRequestHandler implements RequestHandlerInterface
{
    private $decorated;
    private $container;
    private $kernel;

    public function __construct(RequestHandlerInterface $decorated, KernelInterface $kernel, ContainerInterface $container)
    {
        $this->decorated = $decorated;
        $this->container = $container;
        $this->kernel = $kernel;
    }

    /**
     * {@inheritdoc}
     */
    public function boot(array $runtimeConfiguration = []): void
    {
        $this->decorated->boot($runtimeConfiguration);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, Response $response): void
    {
        if ($this->kernel->isDebug()) {
            ServerUtils::hijackProperty($this->kernel, 'startTime', \microtime(true));
        }

        $this->decorated->handle($request, $response);

        if ($this->kernel->isDebug()) {
            if ($this->container->has('debug.stopwatch')) {
                $this->container->get('debug.stopwatch')->reset();
            }

            if ($this->container->has('profiler')) {
                $profiler = $this->container->get('profiler');
                $profiler->reset();
                $profiler->enable();
            }
        }
    }
}