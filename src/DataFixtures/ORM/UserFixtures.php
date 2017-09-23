<?php
declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    /**
     * Users to create for tests
     */
    private const DEFAULT_USERS = [
        'Super Admin',
        'Admin',
        'Reader',
        'Writer',
        'User',
    ];


    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        foreach (self::DEFAULT_USERS as $defaultUser) {
            $username = strtolower(str_replace(' ', '_', $defaultUser));
            $email = sprintf('%s@%s.pl', $username, $username);
            $role = sprintf('ROLE_%s', strtoupper($username));

            $user = new User();
            $user->setFullname($defaultUser);
            $user->setUsername($username);
            $user->setEmail($email);
            $user->setEnabled(true);
            $user->setRoles([$role]);
            $user->setPlainPassword($username);

            $manager->persist($user);
        }

        $manager->flush();
    }
}