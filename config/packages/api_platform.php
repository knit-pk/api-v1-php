<?php

use App\Bundle\SwooleBundle\Server\ServerUtils;

if (isset($_ENV['VARNISH_URLS'])) {
    $container->loadFromExtension('api_platform', [
        'http_cache' => [
            'invalidation' => [
                'varnish_urls' => ServerUtils::decodeStringAsSet($_ENV['VARNISH_URLS']),
            ],
        ],
    ]);
}
