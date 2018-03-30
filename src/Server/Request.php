<?php

namespace App\Server;

use Swoole\Http\Request as SwooleRequest;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request
{
    /**
     * Creates Symfony request from Swoole request. PHP superglobals must get set
     * here.
     *
     * @param \Swoole\Http\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function createSymfonyRequest(SwooleRequest $request): SymfonyRequest
    {
        $this->setServer($request);

        // Other superglobals
        $_GET = $request->get ?? [];
        $_POST = $request->post ?? [];
        $_COOKIE = $request->cookie ?? [];
        $_FILES = $request->files ?? [];
        $content = $request->rawContent() ?: null;

        $symfonyRequest = new SymfonyRequest(
            $_GET,
            $_POST,
            [],
            $_COOKIE,
            $_FILES,
            $_SERVER,
            $content
        );

        if (0 === \mb_strpos($symfonyRequest->headers->get('Content-Type'), 'application/json')) {
            $data = \json_decode($request->rawContent(), true);
            $symfonyRequest->request->replace(\is_array($data) ? $data : []);
        }

        return $symfonyRequest;
    }

    /**
     * Create $_SERVER superglobal for traditional PHP applications. By default
     * Swoole request contains headers with lower case keys and dash separator
     * instead of underscores and upper case letters which PHP expects in the
     * $_SERVER superglobal. For example:
     * - host: localhost:9501
     * - connection: keep-alive
     * - accept-language: en-US,en;q=0.8,sl;q=0.6.
     *
     * @param \Swoole\Http\Request $request
     */
    public function setServer(SwooleRequest $request): void
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
    }
}
