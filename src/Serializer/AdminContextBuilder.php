<?php

declare(strict_types=1);

namespace App\Serializer;

use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use App\Serializer\Group\Factory\AdminSerializerGroupFactory;
use DomainException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class AdminContextBuilder implements SerializerContextBuilderInterface
{
    private $decorated;
    private $authorizationChecker;
    private $adminSerializerGroupFactory;

    public function __construct(SerializerContextBuilderInterface $decorated, AuthorizationCheckerInterface $authorizationChecker, AdminSerializerGroupFactory $adminSerializerGroupFactory)
    {
        $this->decorated = $decorated;
        $this->authorizationChecker = $authorizationChecker;
        $this->adminSerializerGroupFactory = $adminSerializerGroupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function createFromRequest(Request $request, bool $normalization, array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        if (!isset($context['groups']) || !$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            return $context;
        }

        if ($normalization) {
            $generalGroup = 'read';
        } else {
            $generalGroup = 'write';
            switch ($request->getMethod()) {
                case 'PUT':
                    $group = 'update';
                    break;
                case 'POST':
                    $group = 'create';
                    break;
                default:
                    throw new DomainException(\sprintf('Unsupported HTTP method for Admin context denormalization: %s.', $request->getMethod()));
            }
            $context['groups'][] = $this->adminSerializerGroupFactory->createAdminGroup($context['resource_class'], $group);
        }
        $context['groups'][] = $this->adminSerializerGroupFactory->createAdminGroup($context['resource_class'], $generalGroup);

        return $context;
    }
}
