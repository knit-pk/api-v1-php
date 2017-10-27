<?php
declare(strict_types=1);

namespace App\Serializer;

use ApiPlatform\Core\Exception\RuntimeException;
use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use DomainException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class AdminContextBuilder implements SerializerContextBuilderInterface
{
    /**
     * @var \ApiPlatform\Core\Serializer\SerializerContextBuilderInterface
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
     * AdminContextBuilder constructor.
     *
     * @param \ApiPlatform\Core\Serializer\SerializerContextBuilderInterface               $decorated
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(SerializerContextBuilderInterface $decorated, AuthorizationCheckerInterface $authorizationChecker, AdminSerializerGroupFactory $adminSerializerGroupFactory)
    {
        $this->decorated = $decorated;
        $this->authorizationChecker = $authorizationChecker;
        $this->adminSerializerGroupFactory = $adminSerializerGroupFactory;
    }


    /**
     * Creates a serialization context from a Request.
     *
     * @param Request    $request
     * @param bool       $normalization true | false = denormalization
     * @param array|null $extractedAttributes
     *
     * @throws RuntimeException
     *
     * @return array
     * @throws \DomainException
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
                    throw new DomainException(sprintf('Unsupported http method by AdminContextBuilder: %s.', $request->getMethod()));
            }
            $context['groups'][] = $this->adminSerializerGroupFactory->createAdminGroup($context['resource_class'], $group);
        }
        $context['groups'][] = $this->adminSerializerGroupFactory->createAdminGroup($context['resource_class'], $generalGroup);

        return $context;
    }
}