<?php

declare(strict_types=1);

namespace App\Security\User;

use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class JWTUser implements UserInterface, JWTUserInterface
{
    private $id;
    private $username;
    private $roles;

    public function __construct(string $id, string $username, array $roles)
    {
        $this->id = Uuid::fromString($id);
        $this->username = $username;
        $this->roles = $roles;
    }

    /**
     * {@inheritdoc}
     */
    public static function createFromPayload($username, array $payload): self
    {
        return new self(
            $payload['id'],
            $username,
            $payload['roles']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function isUser(?UserInterface $user): bool
    {
        return $user instanceof UserInterface && $this->id->equals($user->getId());
    }
}
