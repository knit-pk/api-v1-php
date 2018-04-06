<?php

declare(strict_types=1);

namespace App\Metadata\Resource\Factory;

use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Resource\ResourceMetadata;
use App\Security\AuthorizationChecker\AuthorizationCheckerInterface;
use App\Serializer\Group\Factory\AdminSerializerGroupFactory;

final class AdminResourceMetadataFactory implements ResourceMetadataFactoryInterface
{
    private $decorated;
    private $authorizationChecker;
    private $adminSerializerGroupFactory;

    public function __construct(ResourceMetadataFactoryInterface $decorated, AuthorizationCheckerInterface $authorizationChecker, AdminSerializerGroupFactory $adminSerializerGroupFactory)
    {
        $this->decorated = $decorated;
        $this->authorizationChecker = $authorizationChecker;
        $this->adminSerializerGroupFactory = $adminSerializerGroupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $resourceClass): ResourceMetadata
    {
        $resourceMetadata = $this->decorated->create($resourceClass);

        if (!$this->authorizationChecker->isAdminAccessGranted()) {
            return $resourceMetadata;
        }

        $attributes = $resourceMetadata->getAttributes();
        if (isset($attributes['normalization_context']['groups'])) {
            $attributes['normalization_context']['groups'][] = $this->adminSerializerGroupFactory->createAdminGroup($resourceClass, 'read');

            $resourceMetadata = $resourceMetadata->withAttributes($attributes);
        }

        if (isset($attributes['denormalization_context']['groups'])) {
            $attributes['denormalization_context']['groups'][] = $this->adminSerializerGroupFactory->createAdminGroup($resourceClass, 'write');

            return $resourceMetadata->withAttributes($attributes);
        }

        $itemOperations = $resourceMetadata->getItemOperations();
        if (isset($itemOperations['put']['denormalization_context'])) {
            $itemOperations['put']['denormalization_context']['groups'][] = $this->adminSerializerGroupFactory->createAdminGroup($resourceClass, 'update');
            $resourceMetadata = $resourceMetadata->withItemOperations($itemOperations);
        }

        $collectionOperations = $resourceMetadata->getCollectionOperations();
        if (isset($collectionOperations['post']['denormalization_context'])) {
            $collectionOperations['post']['denormalization_context']['groups'][] = $this->adminSerializerGroupFactory->createAdminGroup($resourceClass, 'create');
            $resourceMetadata = $resourceMetadata->withCollectionOperations($collectionOperations);
        }

        return $resourceMetadata;
    }
}
