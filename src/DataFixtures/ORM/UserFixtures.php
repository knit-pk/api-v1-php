<?php
declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public const DEFAULT_USERS = [
        'Super Admin'     => [
            USER::ROLE_SUPER_ADMIN,
        ],
        'Admin'           => [
            USER::ROLE_ADMIN,
        ],
        'Reader'          => [
            USER::ROLE_READER,
        ],
        'Writer'          => [
            USER::ROLE_WRITER,
        ],
        'User'            => [
            USER::ROLE_USER,
        ],
        'User Writer'     => [
            USER::ROLE_USER_WRITER,
        ],
        'Articles Author' => [
            USER::ROLE_USER,
        ],
    ];

    public const PUBLIC_USERNAMES = [
        'reader',
        'writer',
        'user',
        'user_writer',
        'articles_author',
    ];


    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     *
     * @throws \DomainException
     */
    public function load(ObjectManager $manager): void
    {
        $publicUsers = array_flip(self::PUBLIC_USERNAMES);

        foreach (self::DEFAULT_USERS as $fullname => $roles) {
            $username = strtolower(str_replace(' ', '_', $fullname));
            $email = sprintf('%s@%s.pl', $username, $username);

            $user = new User();
            $user->setFullname($fullname);
            $user->setUsername($username);
            $user->setEmail($email);
            $user->setEnabled(true);
            $user->setPlainPassword($username);

            foreach ($roles as $role) {
                $securityRole = $this->getReference(sprintf('security-%s', strtolower($role)));
                $user->addSecurityRole($securityRole);
            }

            // Add default role
            $user->addSecurityRole($this->getReference(sprintf('security-%s', strtolower(USER::ROLE_DEFAULT))));

            if (isset($publicUsers[$username])) {
                $this->setReference(sprintf('user-%s', $username), $user);
            }

            $manager->persist($user);
        }

        $manager->flush();
    }


    public function getDependencies(): array
    {
        return [
            SecurityRoleFixtures::class,
        ];
    }
}