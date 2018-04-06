<?php

declare(strict_types=1);

namespace App\Tests\Metadata\Resource\Factory;

use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Resource\ResourceMetadata;
use App\Metadata\Resource\Factory\AdminResourceMetadataFactory;
use App\Security\AuthorizationChecker\AuthorizationCheckerInterface;
use App\Serializer\Group\Factory\AdminSerializerGroupFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class AdminResourceMetadataFactoryTest extends TestCase
{
    private $resourceMetadataFactoryProphecy;

    private $authorizationCheckerProphecy;

    private $adminSerializerGroupFactoryProphecy;

    private $adminResourceMetadataFactory;

    protected function setUp(): void
    {
        $this->resourceMetadataFactoryProphecy = $this->prophesize(ResourceMetadataFactoryInterface::class);
        $this->authorizationCheckerProphecy = $this->prophesize(AuthorizationCheckerInterface::class);
        $this->adminSerializerGroupFactoryProphecy = $this->prophesize(AdminSerializerGroupFactory::class);

        /** @var ResourceMetadataFactoryInterface $resourceMetadataFactoryMock */
        $resourceMetadataFactoryMock = $this->resourceMetadataFactoryProphecy->reveal();

        /** @var AuthorizationCheckerInterface $authorizationCheckerMock */
        $authorizationCheckerMock = $this->authorizationCheckerProphecy->reveal();

        /** @var AdminSerializerGroupFactory $adminSerializerGroupFactoryMock */
        $adminSerializerGroupFactoryMock = $this->adminSerializerGroupFactoryProphecy->reveal();

        $this->adminResourceMetadataFactory = new AdminResourceMetadataFactory($resourceMetadataFactoryMock, $authorizationCheckerMock, $adminSerializerGroupFactoryMock);
    }

    public function noContextProphecyProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider noContextProphecyProvider
     *
     * @param bool $result
     */
    public function testNoContext(bool $result): void
    {
        $resourceClass = 'Test';
        $itemOperations = [];
        $collectionOperations = [];
        $attributes = [];

        $resourceMetadata = new ResourceMetadata(null, null, null, $itemOperations, $collectionOperations, $attributes);

        $this->resourceMetadataFactoryProphecy->create($resourceClass)->willReturn($resourceMetadata)->shouldBeCalled();
        $this->authorizationCheckerProphecy->isAdminAccessGranted()->willReturn($result)->shouldBeCalled();
        $this->adminSerializerGroupFactoryProphecy->createAdminGroup(Argument::any())->shouldNotBeCalled();

        $newResourceMetadata = $this->adminResourceMetadataFactory->create($resourceClass);

        $this->assertSame($resourceMetadata->getItemOperations(), $newResourceMetadata->getItemOperations());
        $this->assertSame($resourceMetadata->getCollectionOperations(), $newResourceMetadata->getCollectionOperations());
        $this->assertSame($resourceMetadata->getAttributes(), $newResourceMetadata->getAttributes());
    }

    public function testAttributesNormalizationWithDenormalization(): void
    {
        $resourceClass = 'Test';
        $itemOperations = [];
        $collectionOperations = [];
        $attributes = [
            'normalization_context' => [
                'groups' => [
                    'TestRead',
                ],
            ],
            'denormalization_context' => [
                'groups' => [
                    'TestWrite',
                ],
            ],
        ];

        $resourceMetadata = new ResourceMetadata(null, null, null, $itemOperations, $collectionOperations, $attributes);

        $this->resourceMetadataFactoryProphecy->create($resourceClass)->willReturn($resourceMetadata)->shouldBeCalled();
        $this->authorizationCheckerProphecy->isAdminAccessGranted()->willReturn(true)->shouldBeCalled();

        $group = 'TestAdminRead';
        $attributes['normalization_context']['groups'][] = $group;
        $resourceMetadata = $resourceMetadata->withAttributes($attributes);

        $this->adminSerializerGroupFactoryProphecy->createAdminGroup($resourceClass, 'read')->willReturn($group)->shouldBeCalled();

        $group = 'TestAdminWrite';
        $attributes['denormalization_context']['groups'][] = $group;
        $resourceMetadata = $resourceMetadata->withAttributes($attributes);

        $this->adminSerializerGroupFactoryProphecy->createAdminGroup($resourceClass, 'write')->willReturn($group)->shouldBeCalled();

        $this->adminSerializerGroupFactoryProphecy->createAdminGroup($resourceClass, 'update')->shouldNotBeCalled();
        $this->adminSerializerGroupFactoryProphecy->createAdminGroup($resourceClass, 'create')->shouldNotBeCalled();

        $newResourceMetadata = $this->adminResourceMetadataFactory->create($resourceClass);

        $this->assertSame($resourceMetadata->getItemOperations(), $newResourceMetadata->getItemOperations());
        $this->assertSame($resourceMetadata->getCollectionOperations(), $newResourceMetadata->getCollectionOperations());
        $this->assertSame($resourceMetadata->getAttributes(), $newResourceMetadata->getAttributes());
    }

    public function testAttributesNormalizationWithOperationsDenormalization(): void
    {
        $resourceClass = 'Test';
        $itemOperations = [
            'put' => [
                'denormalization_context' => [
                    'groups' => [
                        'TestUpdate',
                    ],
                ],
            ],
        ];
        $collectionOperations = [
            'post' => [
                'denormalization_context' => [
                    'groups' => [
                        'TestCreate',
                    ],
                ],
            ],
        ];
        $attributes = [
            'normalization_context' => [
                'groups' => [
                    'TestRead',
                    'TestReadLess',
                ],
            ],
        ];

        $resourceMetadata = new ResourceMetadata(null, null, null, $itemOperations, $collectionOperations, $attributes);

        $this->resourceMetadataFactoryProphecy->create($resourceClass)->willReturn($resourceMetadata)->shouldBeCalled();
        $this->authorizationCheckerProphecy->isAdminAccessGranted()->willReturn(true)->shouldBeCalled();

        $group = 'TestAdminRead';
        $attributes['normalization_context']['groups'][] = $group;
        $resourceMetadata = $resourceMetadata->withAttributes($attributes);

        $this->adminSerializerGroupFactoryProphecy->createAdminGroup($resourceClass, 'read')->willReturn($group)->shouldBeCalled();

        $group = 'TestAdminUpdate';
        $itemOperations['put']['denormalization_context']['groups'][] = $group;
        $resourceMetadata = $resourceMetadata->withItemOperations($itemOperations);

        $this->adminSerializerGroupFactoryProphecy->createAdminGroup($resourceClass, 'update')->willReturn($group)->shouldBeCalled();

        $group = 'TestAdminCreate';
        $collectionOperations['post']['denormalization_context']['groups'][] = $group;
        $resourceMetadata = $resourceMetadata->withCollectionOperations($collectionOperations);

        $this->adminSerializerGroupFactoryProphecy->createAdminGroup($resourceClass, 'create')->willReturn($group)->shouldBeCalled();

        $this->adminSerializerGroupFactoryProphecy->createAdminGroup($resourceClass, 'write')->shouldNotBeCalled();

        $newResourceMetadata = $this->adminResourceMetadataFactory->create($resourceClass);

        $this->assertSame($resourceMetadata->getItemOperations(), $newResourceMetadata->getItemOperations());
        $this->assertSame($resourceMetadata->getCollectionOperations(), $newResourceMetadata->getCollectionOperations());
        $this->assertSame($resourceMetadata->getAttributes(), $newResourceMetadata->getAttributes());
    }
}
