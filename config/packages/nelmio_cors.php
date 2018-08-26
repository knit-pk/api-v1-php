<?php

declare(strict_types=1);

use App\Bundle\SwooleBundle\Functions\ServerUtils;

if (isset($_ENV['CORS_ALLOW_ORIGINS'])) {
    $container->loadFromExtension('nelmio_cors', [
        'defaults' => [
            'allow_origin' => ServerUtils::decodeStringAsSet($_ENV['CORS_ALLOW_ORIGINS']),
        ],
    ]);
}
