<?php

declare(strict_types=1);

namespace App\UrlGenerator;

class ConfigurationUrlGenerator implements UrlGeneratorInterface
{
    private $decorated;
    private $baseUrl;

    public function __construct(UrlGeneratorInterface $decorated, string $baseUrl)
    {
        $this->decorated = $decorated;
        $this->baseUrl = $baseUrl;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    public function generate(string $path): string
    {
        if ('' !== $this->baseUrl) {
            return \sprintf('%s/%s', \rtrim($this->baseUrl, '/'), \ltrim($path, '\\/'));
        }

        return $this->decorated->generate($path);
    }
}
