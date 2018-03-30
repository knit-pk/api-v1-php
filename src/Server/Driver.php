<?php

namespace App\Server;

use App\Kernel;
use Psr\Log\LoggerInterface;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

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
    private $kernel;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $env;

    /**
     * @var bool
     */
    private $debug;

    /**
     * Driver constructor.
     *
     * @param string                   $env
     * @param bool                     $debug
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $env, bool $debug, LoggerInterface $logger)
    {
        $this->env = $env;
        $this->debug = $debug;
        $this->logger = $logger;

        if ($trustedHostsSet = $_SERVER['APP_TRUSTED_HOSTS'] ?? false) {
            $trustedHosts = $this->decodeStringSet($trustedHostsSet);
            $this->logger->info('Setting trusted hosts', $trustedHosts);
            SymfonyRequest::setTrustedHosts($trustedHosts);
        }

        if ($trustedProxiesSet = $_SERVER['APP_TRUSTED_PROXIES'] ?? false) {
            $trustedProxies = $this->decodeStringSet($trustedProxiesSet);
            $this->logger->info('Setting trusted proxies', $trustedProxies);
            SymfonyRequest::setTrustedProxies($trustedProxies, SymfonyRequest::HEADER_X_FORWARDED_ALL);
        }
    }

    /**
     * Boot Symfony Application.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function boot(): void
    {
        $this->kernel = $app = new Kernel($this->env, $this->debug);

        ServerUtils::bindAndCall(function () use ($app) {
            // init bundles
            $app->initializeBundles();
            // init container
            $app->initializeContainer();
        }, $app);

        ServerUtils::bindAndCall(function () use ($app) {
            foreach ($app->getBundles() as $bundle) {
                $bundle->setContainer($app->container);
                $bundle->boot();
            }
            $app->booted = true;
        }, $app);
    }

    /**
     * Does some necessary preparation before each request.
     */
    private function preHandle(): void
    {
        // Reset Kernel startTime, so Symfony can correctly calculate the execution time
        ServerUtils::hijackProperty($this->kernel, 'startTime', \microtime(true));
    }

    /**
     * Happens after each request.
     *
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     */
    private function postHandle(): void
    {
        $container = $this->kernel->getContainer();

        //resets stopwatch, so it can correctly calculate the execution time
        if ($container->has('debug.stopwatch')) {
            $container->get('debug.stopwatch')->__construct();
        }

        if ($container->has('doctrine.orm.entity_manager')) {
            $container->get('doctrine.orm.entity_manager')->clear();
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
                ServerUtils::bindAndCall(function () {
                    //$logger: \Doctrine\DBAL\Logging\DebugStack
                    foreach ($this->loggers as $logger) {
                        ServerUtils::hijackProperty($logger, 'queries', []);
                    }
                }, $profiler->get('db'), [], 'Symfony\Bridge\Doctrine\DataCollector\DoctrineDataCollector');
            }

            // EventDataCollector
            if ($profiler->has('events')) {
                ServerUtils::hijackProperty($profiler->get('events'), 'data', [
                    'called_listeners' => [],
                    'not_called_listeners' => [],
                ]);
            }

            // TwigDataCollector
            if ($profiler->has('twig')) {
                ServerUtils::bindAndCall(function () {
                    ServerUtils::hijackProperty($this->profile, 'profiles', []);
                }, $profiler->get('twig'));
            }

            // Logger
            if ($container->has('logger')) {
                $logger = $container->get('logger');
                ServerUtils::bindAndCall(function () {
                    if (\method_exists($this, 'getDebugLogger') && $debugLogger = $this->getDebugLogger()) {
                        //DebugLogger
                        ServerUtils::hijackProperty($debugLogger, 'records', []);
                    }
                }, $logger);
            }
        }

        \gc_collect_cycles();
    }

    /**
     * Transform Symfony request and response to Swoole compatible response.
     *
     * @param \Swoole\Http\Request  $swooleRequest
     * @param \Swoole\Http\Response $swooleResponse
     *
     * @throws \Exception
     */
    public function handle(SwooleRequest $swooleRequest, SwooleResponse $swooleResponse): void
    {
        $this->preHandle();
        $this->logStats('before handle');

        $symfonyRequest = $this->createSymfonyRequest($swooleRequest);
        $symfonyResponse = $this->kernel->handle($symfonyRequest);
        $this->kernel->terminate($symfonyRequest, $symfonyResponse);

        // HTTP status code for response
        $swooleResponse->status($symfonyResponse->getStatusCode());

        // Headers
        foreach ($symfonyResponse->headers->allPreserveCase() as $name => $values) {
            /** @var array $values */
            foreach ($values as $value) {
                $swooleResponse->header($name, $value);
            }
        }

        $swooleResponse->end($symfonyResponse->getContent());

        $this->logStats('after handle');
        $this->postHandle();
    }

    /**
     * @param string $stringSet
     *
     * @return string[]
     */
    private function decodeStringSet(string $stringSet): array
    {
        $stringSet = \str_replace(['\'', '[', ']'], '', $stringSet);

        return \explode(',', $stringSet);
    }

    private function createSymfonyRequest(SwooleRequest $request): SymfonyRequest
    {
        $headers = [];

        foreach ($request->header as $key => $value) {
            if ('x-forwarded-proto' === $key && 'https' === $value) {
                $request->server['HTTPS'] = 'on';
            }

            $headerKey = 'HTTP_'.\mb_strtoupper(\str_replace('-', '_', $key));
            $headers[$headerKey] = $value;
        }

        // Make swoole's server's keys uppercased and merge them into the $_SERVER superglobal
        $_SERVER = \array_change_key_case(\array_merge($request->server, $headers), CASE_UPPER);

        // Other superglobals
        $_GET = $request->get ?? [];
        $_POST = $request->post ?? [];
        $_COOKIE = $request->cookie ?? [];
        $_FILES = $request->files ?? [];

        $symfonyRequest = new SymfonyRequest($_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER, $request->rawContent());

        if (0 === \mb_strpos($symfonyRequest->headers->get('Content-Type'), 'application/json')) {
            $data = \json_decode($request->rawContent(), true);
            $symfonyRequest->request->replace(\is_array($data) ? $data : []);
        }

        return $symfonyRequest;
    }

    public function logStats(string $when): void
    {
        $this->logger->info(\sprintf('Stats %s', $when), [
            'memory_usage' => ServerUtils::formatBytes(ServerUtils::getMemoryUsage()),
            'memory_peak_usage' => ServerUtils::formatBytes(ServerUtils::getPeakMemoryUsage()),
        ]);
    }
}
