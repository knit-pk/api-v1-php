<?php

declare(strict_types=1);

namespace App\Serializer;

use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;

final class UuidContextBuilder implements SerializerContextBuilderInterface
{
    private $decorated;

    public function __construct(SerializerContextBuilderInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * {@inheritdoc}
     */
    public function createFromRequest(Request $request, bool $normalization, array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        if (!$normalization && 'POST' === $request->getMethod()) {
            $context['default_constructor_arguments'][$context['resource_class']]['id'] = Uuid::uuid4();
        }

        return $context;
    }
}
