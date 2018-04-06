<?php

declare(strict_types=1);

namespace App\Security\AuthorizationChecker;

use App\Security\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface as SymfonyAuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

final class ConsoleSafeAuthorizationChecker implements AuthorizationCheckerInterface
{
    private $authorizationChecker;

    public function __construct(SymfonyAuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted($attributes, $subject = null): bool
    {
        try {
            return $this->authorizationChecker->isGranted($attributes, $subject);
        } catch (AuthenticationCredentialsNotFoundException $e) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isAdminAccessGranted($subject = null): bool
    {
        return $this->isGranted(UserInterface::ROLE_ADMIN, $subject);
    }
}
