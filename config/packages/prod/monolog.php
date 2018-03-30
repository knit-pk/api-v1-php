<?php

use Symfony\Component\Config\Resource\ClassExistenceResource;
use Symfony\Component\Console\Application;

$handlers = [
    'main' => [
        'type' => 'fingers_crossed',
        'action_level' => 'error',
        'handler' => 'nested',
    ],
    'nested' => [
        'type' => 'stream',
        'path' => '%kernel.logs_dir%/%kernel.environment%.log',
        'level' => 'debug',
    ],
    'stdout' => [
        'type'     => 'stream',
        'path'     => 'php://stdout',
        'level'    => 'error',
    ],
    'redis'         => [
        'id'    => 'monolog.handler.redis_handler',
        'type'  => 'service',
        'level' => 'info',
    ],
];

$container->addResource(new ClassExistenceResource(Application::class));
if (class_exists(Application::class)) {
    $handlers['console'] = [
        'type' => 'console',
        'process_psr_3_messages' => false,
        'channels' => ['!event', '!doctrine'],
    ];
}

$container->loadFromExtension('monolog', [
    'handlers' => $handlers,
]);
