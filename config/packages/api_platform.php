<?php

declare(strict_types=1);

use function App\Bundle\SwooleBundle\Functions\decode_string_as_set;

if (isset($_ENV['VARNISH_URLS'])) {
    $container->loadFromExtension('api_platform', [
        'http_cache' => [
            'invalidation' => [
                'varnish_urls' => decode_string_as_set($_ENV['VARNISH_URLS']),
            ],
        ],
    ]);
}
