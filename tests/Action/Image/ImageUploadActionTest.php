<?php

declare(strict_types=1);

namespace App\Tests\Action\Image;

use App\Action\Image\ImageUploadAction;
use App\Entity\Image;
use App\Entity\User;
use App\Security\UserProvider\UserEntityProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class ImageUploadActionTest extends TestCase
{
    /**
     * @var UserEntityProvider|\Prophecy\Prophecy\ObjectProphecy
     */
    private $userEntityProviderProphecy;

    protected function setUp(): void
    {
        $this->userEntityProviderProphecy = $this->prophesize(UserEntityProvider::class);
    }

    public function testRunSuccessAction(): void
    {
        $originalName = 'avatar.png';
        $uploadedFile = new UploadedFile(__DIR__.'/../../../src/DataFixtures/Resources/images/avatar.png', $originalName, null, null, null, true);
        $request = new Request([], [], [], [], ['image' => $uploadedFile]);

        /** @var UserInterface $userMock */
        $userMock = $this->prophesize(UserInterface::class)->reveal();

        $userEntityMock = $this->assertGotUserEntity($userMock);
        $image = $this->runAction($request, $userMock);

        $this->assertSame($uploadedFile, $image->getFile());
        $this->assertSame($userEntityMock, $image->getAuthor());
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\HttpException
     * @expectedExceptionMessage Field `image` is required, and must be a file
     */
    public function testRunActionNoFile(): void
    {
        $request = new Request();

        /** @var UserInterface $userMock */
        $userMock = $this->prophesize(UserInterface::class)->reveal();

        $this->runAction($request, $userMock);
    }

    private function assertGotUserEntity(UserInterface $user): User
    {
        /** @var \App\Entity\User $userEntityMock */
        $userEntityMock = $this->prophesize(User::class)->reveal();

        $this->userEntityProviderProphecy->getReference($user)->willReturn($userEntityMock)->shouldBeCalled();

        return $userEntityMock;
    }

    private function runAction(Request $request, UserInterface $user): Image
    {
        /** @var UserEntityProvider $userEntityProviderMock */
        $userEntityProviderMock = $this->userEntityProviderProphecy->reveal();

        return (new ImageUploadAction($userEntityProviderMock))($request, $user);
    }
}
