<?php
declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class JWTCreatedListener
{
    /**
     * Add custom payload to JWT Access Token
     *
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        /**
         * @var User $user
         */
        $user = $event->getUser();

        $payload = $event->getData();
        $payload['id'] = (string) $user->getId();

        $event->setData($payload);
    }
}