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
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        foreach (self::SECURITY_ROLES as $role) {
            $securityRole = new SecurityRole($role);

            $this->setReference(sprintf('security-%s', strtolower($role)), $securityRole);

            $manager->persist($securityRole);
        }

        $manager->flush();
    }


    public function isSecurityRole()
    {

    }
}