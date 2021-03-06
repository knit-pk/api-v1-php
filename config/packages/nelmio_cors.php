<?php

declare(strict_types=1);

use function K911\Swoole\decode_string_as_set;

if (isset($_ENV['CORS_ALLOW_ORIGINS'])) {
    $container->loadFromExtension('nelmio_cors', [
        'defaults' => [
            'allow_origin' => decode_string_as_set($_ENV['CORS_ALLOW_ORIGINS']),
        ],
    ]);
}
