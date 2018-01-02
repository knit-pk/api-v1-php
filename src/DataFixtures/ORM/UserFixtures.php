<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public const DEFAULT_USERS = [
        'Super Admin' => [
            User::ROLE_SUPER_ADMIN,
        ],
        'Admin' => [
            User::ROLE_ADMIN,
        ],
        'Reader' => [
            User::ROLE_READER,
        ],
        'Writer' => [
            User::ROLE_WRITER,
        ],
        'User' => [
            User::ROLE_USER,
        ],
        'User Writer' => [
            User::ROLE_USER_WRITER,
        ],
        'Articles Author' => [
            User::ROLE_USER,
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
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     *
     * @throws \DomainException
     */
    public function load(ObjectManager $manager): void
    {
        $publicUsers = array_flip(self::PUBLIC_USERNAMES);

        /** @var \App\Entity\SecurityRole $defaultRole */
        $defaultRole = $this->getReference(sprintf('security-%s', strtolower(USER::ROLE_DEFAULT)));

        /** @var \App\Entity\Image $avatar */
        $avatar = $this->getReference('image-avatar.png');

        /**
         * @var string
         * @var array  $roles
         */
        foreach (self::DEFAULT_USERS as $fullname => $roles) {
            $username = strtolower(str_replace(' ', '_', $fullname));
            $email = sprintf('%s@%s.pl', $username, strtolower(str_replace(' ', '-', $fullname)));

            $user = new User();
            $user->setFullname($fullname);
            $user->setUsername($username);
            $user->setEmail($email);
            $user->setEnabled(true);
            $user->setPlainPassword($username);
            $user->setAvatar($avatar);

            foreach ($roles as $role) {
                /** @var \App\Entity\SecurityRole $securityRole */
                $securityRole = $this->getReference(sprintf('security-%s', strtolower($role)));
                $user->addSecurityRole($securityRole);
            }

            // Add default role
            $user->addSecurityRole($defaultRole);

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
            ImageFixtures::class,
        ];
    }
}
