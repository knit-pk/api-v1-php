<?php

declare(strict_types=1);

namespace App\Tests\Security\User;

use App\Security\User\JWTUser;
use App\Security\User\UserInterface;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class JWTUserTest extends TestCase
{
    public function testCreate(): void
    {
        $username = 'username';
        $id = (string) Uuid::uuid4();
        $roles = [UserInterface::ROLE_USER];

        $user = new JWTUser($id, $username, $roles);

        $this->assertSame($username, $user->getUsername());
        $this->assertSame($id, (string) $user->getId());
        $this->assertSame($roles, $user->getRoles());

        $this->assertSame('', $user->getPassword());
        $this->assertSame('', $user->getSalt());
        $user->eraseCredentials();
    }

    public function testCreateFromPayload(): void
    {
        $username = 'username';
        $payload = [
            'id' => (string) Uuid::uuid4(),
            'roles' => [UserInterface::ROLE_USER],
        ];

        $user = JWTUser::createFromPayload($username, $payload);

        $this->assertSame($username, $user->getUsername());
        $this->assertSame($payload['id'], (string) $user->getId());
        $this->assertSame($payload['roles'], $user->getRoles());
    }

    public function testIsUser(): void
    {
        $username = 'username';
        $userId = (string) Uuid::uuid4();
        $userTwoId = (string) Uuid::uuid4();
        $roles = [UserInterface::ROLE_USER];

        $user = new JWTUser($userId, $username, $roles);
        $userRef = new JWTUser($userId, $username, $roles);
        $userTwo = new JWTUser($userTwoId, $username, $roles);

        $this->assertFalse($user->isUser(null));

        $this->assertFalse($user->isUser($userTwo));
        $this->assertFalse($userTwo->isUser($user));

        $this->assertTrue($user->isUser($userRef));
        $this->assertTrue($userRef->isUser($user));
    }
}
