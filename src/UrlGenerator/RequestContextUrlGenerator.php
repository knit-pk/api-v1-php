<?php

declare(strict_types=1);

namespace App\UrlGenerator;

use Symfony\Component\Routing\RequestContextAwareInterface;

class RequestContextUrlGenerator implements UrlGeneratorInterface
{
    private $requestContextAware;

    public function __construct(RequestContextAwareInterface $requestContextAware)
    {
        $this->requestContextAware = $requestContextAware;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(string $path): string
    {
        $context = $this->requestContextAware->getContext();

        $realPath = \ltrim(\sprintf('%s/%s', $context->getBaseUrl(), $path), '\/');

        return \rtrim(\sprintf('%s://%s/%s', $context->getScheme(), $context->getHost(), $realPath), '\/');
    }
}
