<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\SecurityRole;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class SecurityRoleFixtures extends Fixture
{
    public const SECURITY_ROLES = [
        User::ROLE_USER,
        User::ROLE_READER,
        User::ROLE_WRITER,
        User::ROLE_USER_WRITER,
        User::ROLE_ADMIN,
        User::ROLE_SUPER_ADMIN,
    ];

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->getSecurityRolesData() as [
                'role' => $role,
                'name' => $name,
            ]) {
            $securityRole = new SecurityRole($role);
            $securityRole->setName($name);

            $this->setReference(\sprintf('security-%s', \mb_strtolower($role)), $securityRole);

            $manager->persist($securityRole);
        }

        $manager->flush();
    }

    public function getSecurityRolesData(): array
    {
        return [
            [
                'name' => 'User',
                'role' => User::ROLE_USER,
            ],
            [
                'name' => 'Reader',
                'role' => User::ROLE_READER,
            ],
            [
                'name' => 'Writer',
                'role' => User::ROLE_WRITER,
            ],
            [
                'name' => 'User Writer',
                'role' => User::ROLE_USER_WRITER,
            ],
            [
                'name' => 'Admin',
                'role' => User::ROLE_ADMIN,
            ],
            [
                'name' => 'Super Admin',
                'role' => User::ROLE_SUPER_ADMIN,
            ],
        ];
    }
}
