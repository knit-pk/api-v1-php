<?php

declare(strict_types=1);

namespace App\Tests\Security\AuthorizationChecker;

use App\Security\AuthorizationChecker\ConsoleSafeAuthorizationChecker;
use App\Security\User\UserInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

class ConsoleSafeAuthorizationCheckerTest extends TestCase
{
    /**
     * @var AuthorizationCheckerInterface|ObjectProphecy
     */
    private $authorizationCheckerProphecy;

    /**
     * @var ConsoleSafeAuthorizationChecker
     */
    private $consoleSafeAuthorizationChecker;

    protected function setUp(): void
    {
        $this->authorizationCheckerProphecy = $this->prophesize(AuthorizationCheckerInterface::class);

        /** @var AuthorizationCheckerInterface $authorizationCheckerMock */
        $authorizationCheckerMock = $this->authorizationCheckerProphecy->reveal();

        $this->consoleSafeAuthorizationChecker = new ConsoleSafeAuthorizationChecker($authorizationCheckerMock);
    }

    public function testIsGranted(): void
    {
        $attributes = ['some', 'attri' => 'buttes'];
        $subjectMock = $this->prophesize('object')->reveal();

        $this->authorizationCheckerProphecy->isGranted($attributes, $subjectMock)->willReturn(true)->shouldBeCalled();

        $this->assertTrue($this->consoleSafeAuthorizationChecker->isGranted($attributes, $subjectMock));
    }

    public function testIsErrorThrownByAuthorizationChecker(): void
    {
        $attributes = ['some', 'attri' => 'buttes'];
        $subjectMock = $this->prophesize('object')->reveal();

        $this->authorizationCheckerProphecy->isGranted($attributes, $subjectMock)->willThrow(AuthenticationCredentialsNotFoundException::class)->shouldBeCalled();

        $this->assertFalse($this->consoleSafeAuthorizationChecker->isGranted($attributes, $subjectMock));
    }

    public function testIsAdminAccessGranted(): void
    {
        $subjectMock = $this->prophesize('object')->reveal();

        $this->authorizationCheckerProphecy->isGranted(UserInterface::ROLE_ADMIN, $subjectMock)->willReturn(true)->shouldBeCalled();

        $this->assertTrue($this->consoleSafeAuthorizationChecker->isAdminAccessGranted($subjectMock));
    }
}
