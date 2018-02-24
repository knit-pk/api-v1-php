<?php

declare(strict_types=1);

namespace App\Tests\Security\UserProvider;

use App\Entity\User;
use App\Security\User\UserInterface;
use App\Security\UserProvider\UserEntityProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

class UserEntityProviderTest extends TestCase
{
    /**
     * @var EntityManagerInterface|\Prophecy\Prophecy\ObjectProphecy
     */
    private $entityManagerProphecy;

    /**
     * @var UserEntityProvider
     */
    private $provider;

    protected function setUp(): void
    {
        $this->entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);

        /** @var EntityManagerInterface $entityManagerMock */
        $entityManagerMock = $this->entityManagerProphecy->reveal();

        $this->provider = new UserEntityProvider($entityManagerMock);
    }

    public function testGetReferenceAlreadyUserEntity(): void
    {
        /** @var User $userEntityMock */
        $userEntityMock = $this->prophesize(User::class)->reveal();

        $this->assertSame($userEntityMock, $this->provider->getReference($userEntityMock));
    }

    /**
     * @expectedException \App\Security\Exception\SecurityException
     * @expectedExceptionCode 500
     * @expectedExceptionMessage Provided user instance must implement App\Security\User\UserInterface
     */
    public function testGetReferenceWrongUserInterface(): void
    {
        /** @var SymfonyUserInterface $userMock */
        $userMock = $this->prophesize(SymfonyUserInterface::class)->reveal();

        $this->provider->getReference($userMock);
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     */
    public function testGetReferenceGotUserEntity(): void
    {
        /** @var UuidInterface $userIdMock */
        $userIdMock = $this->prophesize(UuidInterface::class)->reveal();

        /** @var SymfonyUserInterface|UserInterface|\Prophecy\Prophecy\ObjectProphecy $userProphecy */
        $userProphecy = $this->prophesize(SymfonyUserInterface::class)
            ->willImplement(UserInterface::class);

        $userProphecy->getId()->willReturn($userIdMock)->shouldBeCalled();

        /** @var User $userEntityMock */
        $userEntityMock = $this->prophesize(User::class)->reveal();

        /** @var SymfonyUserInterface $userMock */
        $userMock = $userProphecy->reveal();

        $this->entityManagerProphecy->getReference(User::class, $userIdMock)->willReturn($userEntityMock)->shouldBeCalled();

        $this->assertSame($userEntityMock, $this->provider->getReference($userMock));
    }

    /**
     * @expectedException \App\Security\Exception\SecurityException
     * @expectedExceptionCode 500
     * @expectedExceptionMessage Provided user does not exists in database
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function testGetReferenceUserNotFound(): void
    {
        /** @var UuidInterface $userIdMock */
        $userIdMock = $this->prophesize(UuidInterface::class)->reveal();

        /** @var SymfonyUserInterface|UserInterface|\Prophecy\Prophecy\ObjectProphecy $userProphecy */
        $userProphecy = $this->prophesize(SymfonyUserInterface::class)
            ->willImplement(UserInterface::class);

        $userProphecy->getId()->willReturn($userIdMock)->shouldBeCalled();

        /** @var SymfonyUserInterface $userMock */
        $userMock = $userProphecy->reveal();

        $this->entityManagerProphecy->getReference(User::class, $userIdMock)
            ->willThrow(ORMException::class)
            ->shouldBeCalled();

        $this->provider->getReference($userMock);
    }
}
