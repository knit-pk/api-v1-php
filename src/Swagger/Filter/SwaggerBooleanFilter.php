<?php

declare(strict_types=1);

namespace App\Swagger\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

final class SwaggerBooleanFilter implements FilterInterface
{
    private $decorated;

    public function __construct(FilterInterface $decorated)
    {
        $this->decorated = $decorated;
    }

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

    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        $this->decorated->apply($queryBuilder, $queryNameGenerator, $resourceClass, $operationName);
    }
}
