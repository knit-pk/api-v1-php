<?php
declare(strict_types=1);

namespace App\Swagger\Filter;

use ApiPlatform\Core\Api\FilterInterface;

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
                'type'        => 'boolean',
                'description' => 'Filter: Boolean',
            ];
        }

        return $description;
    }
}