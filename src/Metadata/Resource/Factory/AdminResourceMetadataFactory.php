<?php
declare(strict_types=1);

namespace App\Metadata\Resource\Factory;

use ApiPlatform\Core\Exception\ResourceClassNotFoundException;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Resource\ResourceMetadata;
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
     * AdminResourceMetadataFactory constructor.
     *
     * @param \ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface $decorated
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(ResourceMetadataFactoryInterface $decorated, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->decorated = $decorated;
        $this->authorizationChecker = $authorizationChecker;
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

        $attributes = $resourceMetadata->getAttributes();
        if (!isset($attributes['normalization_context']['groups'], $attributes['denormalization_context']['groups']) || !$this->isGranted()) {
            return $resourceMetadata;
        }

        $attributes['normalization_context']['groups'][] = 'AdminRead';
        $attributes['denormalization_context']['groups'][] = 'AdminWrite';

        return $resourceMetadata->withAttributes($attributes);
    }


    /**
     * Safely check if authenticated as admin.
     * Remarks: Handled error is thrown when warming up cache.
     *
     * @return bool
     */
    private function isGranted(): bool
    {
        try {
            return $this->authorizationChecker->isGranted('ROLE_ADMIN');
        } catch (AuthenticationCredentialsNotFoundException $e) {
            return false;
        }
    }
}