<?php

declare(strict_types=1);

use Symfony\Component\Config\Resource\ClassExistenceResource;
use Symfony\Component\Console\Application;

$handlers = [
    'main' => [
        'type' => 'stream',
        'path' => '%kernel.logs_dir%/%kernel.environment%.log',
        'level' => 'debug',
        'channels' => ['!event'],
    ],
    'redis' => [
        'id' => 'monolog.handler.redis_handler',
        'type' => 'service',
        'level' => 'info',
    ],
];

// Log to stdout only if in docker container
if (\array_key_exists('DOCKERIZE_WAIT_FOR', $_ENV)) {
    $handlers['stdout'] = [
        'type' => 'stream',
        'path' => 'php://stdout',
        'level' => 'debug',
        'channels' => ['!event', '!console'],
    ];
}

$container->addResource(new ClassExistenceResource(Application::class));
if (\class_exists(Application::class)) {
    $handlers['console'] = [
        'type' => 'console',
        'process_psr_3_messages' => false,
        'channels' => ['!event', '!doctrine', '!console'],
    ];
}

$container->loadFromExtension('monolog', [
    'handlers' => $handlers,
]);
