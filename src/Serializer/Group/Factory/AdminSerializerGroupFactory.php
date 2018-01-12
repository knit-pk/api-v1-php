<?php

declare(strict_types=1);

namespace App\Serializer\Group\Factory;

class AdminSerializerGroupFactory
{
    /**
     * @param string $resourceClass
     * @param string $postfix
     *
     * @return string
     */
    public function createAdminGroup(string $resourceClass, string $postfix): string
    {
        if ($position = \mb_strrpos($resourceClass, '\\')) {
            $shortName = \mb_substr($resourceClass, 1 + $position);
        }

        return \sprintf('%sAdmin%s', $shortName ?? $resourceClass, \ucfirst($postfix));
    }
}
