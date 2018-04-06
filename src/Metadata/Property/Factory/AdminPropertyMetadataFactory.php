<?php

declare(strict_types=1);

namespace App\Metadata\Property\Factory;

use ApiPlatform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Property\PropertyMetadata;
use App\Security\AuthorizationChecker\AuthorizationCheckerInterface;

final class AdminPropertyMetadataFactory implements PropertyMetadataFactoryInterface
{
    private $decorated;

    private $authorizationChecker;

    public function __construct(PropertyMetadataFactoryInterface $decorated, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->decorated = $decorated;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $resourceClass, string $property, array $options = []): PropertyMetadata
    {
        $options['admin_access'] = $this->authorizationChecker->isAdminAccessGranted();

        return $this->decorated->create($resourceClass, $property, $options);
    }
}
