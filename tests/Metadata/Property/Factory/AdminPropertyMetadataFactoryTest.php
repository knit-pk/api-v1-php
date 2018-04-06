<?php

declare(strict_types=1);

namespace App\Tests\Metadata\Property\Factory;

use ApiPlatform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Property\PropertyMetadata;
use App\Metadata\Property\Factory\AdminPropertyMetadataFactory;
use App\Security\AuthorizationChecker\AuthorizationCheckerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class AdminPropertyMetadataFactoryTest extends TestCase
{
    /**
     * @var PropertyMetadataFactoryInterface|ObjectProphecy
     */
    private $decoratedProphecy;

    /**
     * @var AuthorizationCheckerInterface|ObjectProphecy
     */
    private $authorizationCheckerProphecy;
    /**
     * @var AdminPropertyMetadataFactory
     */
    private $factory;

    protected function setUp(): void
    {
        $this->decoratedProphecy = $this->prophesize(PropertyMetadataFactoryInterface::class);
        $this->authorizationCheckerProphecy = $this->prophesize(AuthorizationCheckerInterface::class);

        /** @var PropertyMetadataFactoryInterface $decoratedMock */
        $decoratedMock = $this->decoratedProphecy->reveal();

        /** @var AuthorizationCheckerInterface $authorizationCheckerMock */
        $authorizationCheckerMock = $this->authorizationCheckerProphecy->reveal();

        $this->factory = new AdminPropertyMetadataFactory($decoratedMock, $authorizationCheckerMock);
    }

    public function adminAccessProvider(): array
    {
        return [
            'granted' => [true],
            'not granted' => [false],
        ];
    }

    /**
     * @dataProvider adminAccessProvider
     *
     * @param bool $adminAccess
     *
     * @throws \ApiPlatform\Core\Exception\PropertyNotFoundException
     */
    public function testCreate(bool $adminAccess): void
    {
        $propertyMetadata = new PropertyMetadata();
        $resourceClass = 'class';
        $resourceProperty = 'property';

        $this->authorizationCheckerProphecy->isAdminAccessGranted()->willReturn($adminAccess)->shouldBeCalled();
        $this->decoratedProphecy->create($resourceClass, $resourceProperty, [
            'admin_access' => $adminAccess,
        ])->willReturn($propertyMetadata)->shouldBeCalled();

        $this->assertSame($propertyMetadata, $this->factory->create($resourceClass, $resourceProperty));
    }
}
