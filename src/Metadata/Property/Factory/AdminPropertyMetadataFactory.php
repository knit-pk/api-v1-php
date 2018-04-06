<?php

declare(strict_types=1);

namespace App\Metadata\Property\Factory;

use ApiPlatform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Property\PropertyMetadata;
use App\Security\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

class AdminPropertyMetadataFactory implements PropertyMetadataFactoryInterface
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
        $options['admin_granted'] = $this->isAdminAccessGranted();

        return $this->decorated->create($resourceClass, $property, $options);
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
            return $this->authorizationChecker->isGranted(UserInterface::ROLE_ADMIN);
        } catch (AuthenticationCredentialsNotFoundException $e) {
            return false;
        }
    }
}
