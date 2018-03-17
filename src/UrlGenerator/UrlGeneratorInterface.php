<?php

declare(strict_types=1);

namespace App\UrlGenerator;

interface UrlGeneratorInterface
{
    /**
     * Generates full url from path.
     *
     * @param string $path
     *
     * @return string
     */
    public function generate(string $path): string;
}
