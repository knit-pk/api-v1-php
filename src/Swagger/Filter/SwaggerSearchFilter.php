<?php

declare(strict_types=1);

namespace App\Swagger\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\ContextAwareFilterInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use DomainException;

final class SwaggerSearchFilter implements ContextAwareFilterInterface
{
    private $decorated;

    public function __construct(ContextAwareFilterInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \DomainException
     */
    public function getDescription(string $resourceClass): array
    {
        $description = $this->decorated->getDescription($resourceClass);

        foreach ($description as $property => $data) {
            $descriptionText = $this->getDescriptionText($data['strategy']);

            if ('[]' === \mb_substr($property, -2)) {
                $descriptionText = \sprintf('Multiple Selection: %1$s. Example usage: ?%2$s[]=%2$s&%2$s[]=%2$s', $descriptionText, \mb_substr($property, 0, -2));
            } else {
                $descriptionText = \sprintf('Filter: %s.', $descriptionText);
            }

            $description[$property]['swagger'] = [
                'type' => 'string',
                'description' => $descriptionText,
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

    private function getDescriptionText(string $strategy): string
    {
        switch ($strategy) {
            case 'exact':
                return 'Exact match - property must match from the beginning to the end';
            case 'partial':
                return 'Partial match - property or its part must consist of provided value';
            case 'end':
                return 'Ends with - property must end with provided value';
                break;
            case 'word_start':
                return 'Begins with - property must start with provided value';
                break;
            default:
                throw new DomainException(\sprintf('Unimplemented description text for search strategy: %s', $strategy));
        }
    }
}
