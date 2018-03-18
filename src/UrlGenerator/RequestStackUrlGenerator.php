<?php

declare(strict_types=1);

namespace App\UrlGenerator;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestStackUrlGenerator implements UrlGeneratorInterface
{
    private $requestStack;
    private $decorated;

    public function __construct(UrlGeneratorInterface $decorated, RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        $this->decorated = $decorated;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(string $path): string
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request instanceof Request) {
            return $request->getUriForPath($path);
        }

        return $this->decorated->generate($path);
    }
}
