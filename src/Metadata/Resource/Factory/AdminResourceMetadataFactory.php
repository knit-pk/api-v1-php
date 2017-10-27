<?php
declare(strict_types=1);

namespace App\Metadata\Resource\Factory;

use ApiPlatform\Core\Exception\ResourceClassNotFoundException;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Resource\ResourceMetadata;
use App\Serializer\AdminSerializerGroupFactory;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

final class AdminResourceMetadataFactory implements ResourceMetadataFactoryInterface
{

    /**
     * @var \ApiPlatform\Core\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface
     */
    private $decorated;

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var \App\Serializer\AdminSerializerGroupFactory
     */
    private $adminSerializerGroupFactory;


    /**
     * AdminResourceMetadataFactory constructor.
     *
     * @param \ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface $decorated
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     * @param \App\Serializer\AdminSerializerGroupFactory                                  $adminSerializerGroupFactory
     */
    public function __construct(ResourceMetadataFactoryInterface $decorated, AuthorizationCheckerInterface $authorizationChecker, AdminSerializerGroupFactory $adminSerializerGroupFactory)
    {
        $this->decorated = $decorated;
        $this->authorizationChecker = $authorizationChecker;
        $this->adminSerializerGroupFactory = $adminSerializerGroupFactory;
    }


    /**
     * Creates a resource metadata.
     *
     * @param string $resourceClass
     *
     * @throws ResourceClassNotFoundException
     *
     * @return ResourceMetadata
     */
    public function create(string $resourceClass): ResourceMetadata
    {
        $resourceMetadata = $this->decorated->create($resourceClass);

        if (!$this->isAdminAccessGranted()) {
            return $resourceMetadata;
        }

        $attributes = $resourceMetadata->getAttributes();
        if (isset($attributes['normalization_context']['groups'])) {
            $attributes['normalization_context']['groups'][] = $this->adminSerializerGroupFactory->createAdminGroup($resourceClass, 'read');
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


    /**
     * Safely check if authenticated as admin.
     * Remarks: Handled error is thrown when warming up cache.
     *
     * @return bool
     */
    private function isAdminAccessGranted(): bool
    {
        try {
            return $this->authorizationChecker->isGranted('ROLE_ADMIN');
        } catch (AuthenticationCredentialsNotFoundException $e) {
            return false;
        }
    }
}