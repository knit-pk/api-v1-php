<?php

namespace App\Server;

use App\Kernel;
use RuntimeException;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

/**
 * Driver for running Symfony with Swoole.
 *
 * @see https://github.com/php-pm/php-pm-httpkernel/blob/master/Bootstraps/Symfony.php
 */
class Driver
{
    /**
     * @var \Symfony\Component\HttpKernel\Kernel
     */
    public $kernel;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    public $symfonyRequest;

    /**
     * @var \Symfony\Component\HttpFoundation\Response
     */
    public $symfonyResponse;

    /**
     * @var \Swoole\Http\Request
     */
    private $swooleRequest;

    /**
     * @var \Swoole\Http\Response
     */
    private $swooleResponse;

    /**
     * Boot Symfony Application.
     *
     * @param string $env   Application environment
     * @param bool   $debug Switches debug mode on/off
     *
     * @throws \Symfony\Component\Dotenv\Exception\PathException
     * @throws \Symfony\Component\Dotenv\Exception\FormatException
     * @throws \RuntimeException
     */
    public function boot($env, $debug): void
    {
        if (!\class_exists(Kernel::class)) {
            throw new RuntimeException('Could not find App\\Kernel class. Make sure you have autoloading configured properly');
        }

        $this->kernel = $app = new Kernel($env, $debug);

        Accessor::bindAndCall(function () use ($app) {
            // init bundles
            $app->initializeBundles();
            // init container
            $app->initializeContainer();
        }, $app);

        Accessor::bindAndCall(function () use ($app) {
            foreach ($app->getBundles() as $bundle) {
                $bundle->setContainer($app->container);
                $bundle->boot();
            }
            $app->booted = true;
        }, $app);
    }

    /**
     * Set Swoole request.
     *
     * @param \Swoole\Http\Request $request
     */
    public function setSwooleRequest(SwooleRequest $request): void
    {
        $this->swooleRequest = $request;
    }

    /**
     * Set Swoole response.
     *
     * @param \Swoole\Http\Response $response
     */
    public function setSwooleResponse(SwooleResponse $response): void
    {
        $this->swooleResponse = $response;
    }

    /**
     * Does some necessary preparation before each request.
     */
    public function preHandle(): void
    {
        // Reset Kernel startTime, so Symfony can correctly calculate the execution time
        Accessor::hijackProperty($this->kernel, 'startTime', \microtime(true));
    }

    /**
     * Happens after each request.
     *
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     */
    public function postHandle(): void
    {
        $container = $this->kernel->getContainer();

        //resets stopwatch, so it can correctly calculate the execution time
        if ($container->has('debug.stopwatch')) {
            $container->get('debug.stopwatch')->__construct();
        }

        //reset all profiler stuff currently supported
        if ($container->has('profiler')) {
            $profiler = $container->get('profiler');

            // since Symfony does not reset Profiler::disable() calls after each request, we need to do it,
            // so the profiler bar is visible after the second request as well.
            $profiler->enable();

            // Doctrine
            // Doctrine\Bundle\DoctrineBundle\DataCollector\DoctrineDataCollector
            if ($profiler->has('db')) {
                Accessor::bindAndCall(function () {
                    //$logger: \Doctrine\DBAL\Logging\DebugStack
                    foreach ($this->loggers as $logger) {
                        Accessor::hijackProperty($logger, 'queries', []);
                    }
                }, $profiler->get('db'), [], 'Symfony\Bridge\Doctrine\DataCollector\DoctrineDataCollector');
            }

            // EventDataCollector
            if ($profiler->has('events')) {
                Accessor::hijackProperty($profiler->get('events'), 'data', [
                    'called_listeners' => [],
                    'not_called_listeners' => [],
                ]);
            }

            // TwigDataCollector
            if ($profiler->has('twig')) {
                Accessor::bindAndCall(function () {
                    Accessor::hijackProperty($this->profile, 'profiles', []);
                }, $profiler->get('twig'));
            }

            // Logger
            if ($container->has('logger')) {
                $logger = $container->get('logger');
                Accessor::bindAndCall(function () {
                    if (\method_exists($this, 'getDebugLogger') && $debugLogger = $this->getDebugLogger()) {
                        //DebugLogger
                        Accessor::hijackProperty($debugLogger, 'records', []);
                    }
                }, $logger);
            }
        }
    }

    /**
     * Transform Symfony request and response to Swoole compatible response.
     *
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function handle(): void
    {
        $rq = new Request();
        $this->symfonyRequest = $rq->createSymfonyRequest($this->swooleRequest);
        $this->symfonyResponse = $this->kernel->handle($this->symfonyRequest);

        // HTTP status code for response
        $this->swooleResponse->status($this->symfonyResponse->getStatusCode());

        // Cookies
        foreach ($this->symfonyResponse->headers->getCookies() as $cookie) {
            /* @var \Symfony\Component\HttpFoundation\Cookie $cookie */
            $this->swooleResponse->cookie(
                $cookie->getName(),
                \urlencode($cookie->getValue()),
                $cookie->getExpiresTime(),
                $cookie->getPath(),
                $cookie->getDomain(),
                $cookie->isSecure(),
                $cookie->isHttpOnly()
            );
        }

        // Headers
        foreach ($this->symfonyResponse->headers->allPreserveCase() as $name => $values) {
            /** @var array $values */
            foreach ($values as $value) {
                $this->swooleResponse->header($name, $value);
            }
        }

        $this->kernel->terminate($this->symfonyRequest, $this->symfonyResponse);
        $this->swooleResponse->end($this->symfonyResponse->getContent());
    }
}
