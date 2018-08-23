<?php

declare(strict_types=1);

namespace App\Swagger\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\ContextAwareFilterInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

final class SwaggerBooleanFilter implements ContextAwareFilterInterface
{
    private $decorated;

    public function __construct(ContextAwareFilterInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        $description = $this->decorated->getDescription($resourceClass);

        foreach ($description as $property => $data) {
            $description[$property]['swagger'] = [
                'type' => 'boolean',
                'description' => 'Filter: Boolean',
            ];
        }

        return $description;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null, array $context = []): void
    {
        $this->decorated->apply($queryBuilder, $queryNameGenerator, $resourceClass, $operationName, $context);
    }
}
