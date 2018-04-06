<?php

declare(strict_types=1);

namespace App\Security\AuthorizationChecker;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface as SymfonyAuthorizationCheckerInterface;

interface AuthorizationCheckerInterface extends SymfonyAuthorizationCheckerInterface
{
    /**
     * Checks if the admin access is granted against the current authentication token and optionally supplied subject.
     *
     * @param mixed $subject
     *
     * @return bool
     */
    public function isAdminAccessGranted($subject = null): bool;
}
