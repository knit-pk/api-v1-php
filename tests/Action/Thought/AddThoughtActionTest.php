<?php

declare(strict_types=1);

namespace App\Tests\Action\Thought;

use App\Action\Thought\AddThoughtAction;
use App\Entity\User;
use App\Security\UserProvider\UserEntityProvider;
use App\Thought\ThoughtfulInterface;
use App\Thought\ThoughtInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class AddThoughtActionTest extends TestCase
{
    /**
     * @var UserEntityProvider|\Prophecy\Prophecy\ObjectProphecy
     */
    private $userEntityProviderProphecy;

    /**
     * @var ThoughtfulInterface|\Prophecy\Prophecy\ObjectProphecy
     */
    private $thoughtfulProphecy;

    /**
     * @var ThoughtInterface|\Prophecy\Prophecy\ObjectProphecy
     */
    private $thoughtProphecy;

    protected function setUp(): void
    {
        $this->userEntityProviderProphecy = $this->prophesize(UserEntityProvider::class);
        $this->thoughtProphecy = $this->prophesize(ThoughtInterface::class);
        $this->thoughtfulProphecy = $this->prophesize(ThoughtfulInterface::class);
    }

    public function testAddThoughtSuccess(): void
    {
        /** @var ThoughtInterface $thoughtMock */
        $thoughtMock = $this->thoughtProphecy->reveal();

        /** @var ThoughtfulInterface $thoughtfulMock */
        $thoughtfulMock = $this->thoughtfulProphecy->reveal();

        /** @var UserInterface $userMock */
        $userMock = $this->prophesize(UserInterface::class)->reveal();

        $this->thoughtfulProphecy->isThoughtSupported($thoughtMock)->willReturn(true)->shouldBeCalled();

        $author = $this->assertGotUserEntity($userMock);
        $this->thoughtProphecy->setAuthor($author)->shouldBeCalled();
        $this->thoughtProphecy->setSubject($thoughtfulMock)->shouldBeCalled();

        $this->assertSame($thoughtMock, $this->runAction($thoughtMock, $thoughtfulMock, $userMock));
    }

    /**
     * @expectedException \App\Thought\Exception\NotSupportedThoughtException
     * @expectedExceptionMessageRegExp  /Object \w+ does not support thought of class \w+. Supported: foo, bar./
     */
    public function testAddNotSupportedThought(): void
    {
        /** @var ThoughtInterface $thoughtMock */
        $thoughtMock = $this->thoughtProphecy->reveal();

        /** @var ThoughtfulInterface $thoughtfulMock */
        $thoughtfulMock = $this->thoughtfulProphecy->reveal();

        /** @var UserInterface $userMock */
        $userMock = $this->prophesize(UserInterface::class)->reveal();

        $this->thoughtfulProphecy->isThoughtSupported($thoughtMock)->willReturn(false)->shouldBeCalled();
        $this->thoughtfulProphecy->getSupportedThoughts()->willReturn(['foo', 'bar'])->shouldBeCalled();

        $this->runAction($thoughtMock, $thoughtfulMock, $userMock);
    }

    private function assertGotUserEntity(UserInterface $user): User
    {
        /** @var \App\Entity\User $userEntityMock */
        $userEntityMock = $this->prophesize(User::class)->reveal();

        $this->userEntityProviderProphecy->getUser($user)->willReturn($userEntityMock)->shouldBeCalled();

        return $userEntityMock;
    }

    private function runAction(ThoughtInterface $data, ThoughtfulInterface $parent, UserInterface $user): ThoughtInterface
    {
        /** @var UserEntityProvider $userEntityProviderMock */
        $userEntityProviderMock = $this->userEntityProviderProphecy->reveal();

        return (new AddThoughtAction($userEntityProviderMock))($data, $parent, $user);
    }
}
