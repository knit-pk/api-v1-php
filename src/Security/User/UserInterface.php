<?php

declare(strict_types=1);

namespace App\Security\User;

use Ramsey\Uuid\UuidInterface;

interface UserInterface
{
    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_READER = 'ROLE_READER';
    public const ROLE_WRITER = 'ROLE_WRITER';
    public const ROLE_USER_WRITER = 'ROLE_USER_WRITER';
    public const ROLE_ADMIN = 'ROLE_ADMIN';

    /**
     * Get an universally unique identifier of user.
     *
     * @return \Ramsey\Uuid\UuidInterface|null
     */
    public function getId(): ?UuidInterface;

    /**
     * Determine whether user giver user instance is the same as self.
     *
     * @param \App\Security\User\UserInterface|null $user
     *
     * @return bool
     */
    public function isUser(?self $user): bool;
}
