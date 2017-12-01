<?php
declare(strict_types=1);

namespace App\Swagger\Filter;

use ApiPlatform\Core\Serializer\Filter\FilterInterface;
use ApiPlatform\Core\Serializer\Filter\GroupFilter;
use DomainException;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;

final class SwaggerGroupFilter implements FilterInterface
{

    private $decorated;

    private $whitelist;


    public function __construct(FilterInterface $decorated)
    {
        $reflection = new ReflectionClass($decorated);

        if (!$decorated instanceof GroupFilter) {
            throw new DomainException(sprintf('Not supported filter instance: %s', $reflection->getShortName()));
        }

        /**
         * Hacks
         */
        $whitelist = $reflection->getProperty('whitelist');
        $whitelist->setAccessible(true);
        $this->whitelist = $whitelist->getValue($decorated) ?? [];

        $this->decorated = $decorated;
    }


    public function getDescription(string $resourceClass): array
    {
        $description = $this->decorated->getDescription($resourceClass);

        $groups = \array_slice($this->whitelist, 0, 3) + ['Group', 'Group'];
        $descriptionText = sprintf('Add group to serialization context. Example usage: ?group[]=%s', implode('&group[]=', $groups));

        foreach ($description as $property => $data) {
            $description[$property]['swagger'] = [
                'description' => $descriptionText,
                'enum'        => $this->whitelist,
                'type'        => 'string',
            ];
        }

        return $description;
    }


    public function apply(Request $request, bool $normalization, array $attributes, array &$context)
    {
        $this->decorated->apply($request, $normalization, $attributes, $context);
    }
}