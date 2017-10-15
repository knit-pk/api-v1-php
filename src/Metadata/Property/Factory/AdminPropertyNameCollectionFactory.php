<?php
declare(strict_types=1);

namespace App\Metadata\Property\Factory;

use ApiPlatform\Core\Exception\ResourceClassNotFoundException;
use ApiPlatform\Core\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface;
use ApiPlatform\Core\Metadata\Property\PropertyNameCollection;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class AdminPropertyNameCollectionFactory implements PropertyNameCollectionFactoryInterface
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
     * AdminPropertyNameCollectionFactory constructor.
     *
     * @param \ApiPlatform\Core\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface $decorated
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface       $authorizationChecker
     */
    public function __construct(PropertyNameCollectionFactoryInterface $decorated, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->decorated = $decorated;
        $this->authorizationChecker = $authorizationChecker;
    }


    /**
     * Creates the property name collection for the given class and options.
     *
     * @param string $resourceClass
     * @param array  $options
     *
     * @throws ResourceClassNotFoundException
     *
     * @return PropertyNameCollection
     */
    public function create(string $resourceClass, array $options = []): PropertyNameCollection
    {
        if (isset($options['serializer_groups']) && $this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $options['serializer_groups'][] = 'AdminRead';
            $options['serializer_groups'][] = 'AdminWrite';
        }

        return $this->decorated->create($resourceClass, $options);
    }
}