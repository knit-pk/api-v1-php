<?php

use App\Bundle\SwooleBundle\Server\ServerUtils;

if (isset($_ENV['CORS_ALLOW_ORIGIN'])) {
    $container->loadFromExtension('nelmio_cors', [
        'defaults' => [
            'allow_origin' => ServerUtils::decodeStringAsSet($_ENV['CORS_ALLOW_ORIGIN']),
        ],
    ]);
}
