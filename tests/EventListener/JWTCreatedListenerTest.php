<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\EventListener\JWTCreatedListener;
use App\Security\User\UserInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

class JWTCreatedListenerTest extends TestCase
{
    /**
     * @var JWTCreatedListener
     */
    private $listener;

    protected function setUp(): void
    {
        $this->listener = new JWTCreatedListener();
    }

    public function testOnJWTCreated(): void
    {
        $userId = Uuid::uuid4();

        /** @var SymfonyUserInterface|UserInterface|\Prophecy\Prophecy\ObjectProphecy $userProphecy */
        $userProphecy = $this->prophesize(SymfonyUserInterface::class)
            ->willImplement(UserInterface::class);

        $userProphecy->getId()->willReturn($userId)->shouldBeCalled();

        /** @var SymfonyUserInterface $userMock */
        $userMock = $userProphecy->reveal();
        $event = new JWTCreatedEvent([], $userMock);

        $this->assertArrayNotHasKey('id', $event->getData());
        $this->listener->onJWTCreated($event);

        $payload = $event->getData();
        $this->assertArrayHasKey('id', $payload);
        $this->assertSame((string) $userId, $payload['id']);
    }

    /**
     * @expectedException \App\Security\Exception\SecurityException
     * @expectedExceptionMessage JWTCreated event must provide user object implementing local UserInterface
     */
    public function testOnJWTCreatedSymfonyUser(): void
    {
        /** @var SymfonyUserInterface $userMock */
        $userMock = $this->prophesize(SymfonyUserInterface::class)->reveal();
        $event = new JWTCreatedEvent([], $userMock);

        $this->listener->onJWTCreated($event);
    }
}
