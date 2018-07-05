<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Security\Exception\SecurityException;
use App\Security\User\UserInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

final class JWTCreatedListener
{
    /**
     * @param \Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent $event
     *
     * @throws \App\Security\Exception\SecurityException
     */
    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof UserInterface) {
            throw new SecurityException('JWTCreated event must provide user object implementing local UserInterface', 500);
        }

        $payload = $event->getData();
        $payload['id'] = $user->getId()->toString();

        $event->setData($payload);
    }
}
