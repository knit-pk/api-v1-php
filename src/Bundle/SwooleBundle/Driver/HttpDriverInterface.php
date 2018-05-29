<?php

declare(strict_types=1);

namespace App\Bundle\SwooleBundle\Driver;

use Swoole\Http\Request;
use Swoole\Http\Response;

interface HttpDriverInterface
{
    /**
     * Override configuration at runtime.
     *
     * @param array $configuration
     */
    public function boot(array $configuration = []): void;

    /**
     * Handles swoole request and modifies swoole response accordingly.
     *
     * @param \Swoole\Http\Request  $request
     * @param \Swoole\Http\Response $response
     */
    public function handle(Request $request, Response $response): void;
}
