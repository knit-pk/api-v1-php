<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Generator;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Yaml\Yaml;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    private const USER_FIXTURES = __DIR__.'/../Resources/fixtures/users.yaml';

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
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     * @throws \DomainException
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->getUserFixtures() as $fixture) {
            $username = $fixture['username'];
            $user = new User(Uuid::uuid4());

            $user->setFullname($fixture['fullname']);
            $user->setUsername($username);
            $user->setEmail($fixture['email']);
            $user->setSuperAdmin($fixture['super_admin']);
            $user->setEnabled($fixture['enabled']);
            $user->setPlainPassword($fixture['password']);

            /** @var \App\Entity\Image $avatar */
            $avatar = $this->getReference($fixture['avatar']);
            $user->setAvatar($avatar);

            /** @var string[] $roles */
            $roles = $fixture['roles'];
            foreach ($roles as $role) {
                /** @var \App\Entity\SecurityRole $securityRole */
                $securityRole = $this->getReference($role);
                $user->addSecurityRole($securityRole);
            }

            if ($fixture['public']) {
                $this->setReference(\sprintf('user-%s', $username), $user);
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

    /**
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     *
     * @return \Generator
     */
    private function getUserFixtures(): Generator
    {
        $fixtures = Yaml::parseFile(self::USER_FIXTURES);

        $defaults = $fixtures['_defaults'];

        /** @var array[] $users */
        $users = $fixtures['users'];
        foreach ($users as $user) {
            $fullname = $user['fullname'];
            $username = $user['username'] ?? \mb_strtolower(\str_replace(' ', '_', $fullname));
            $email = $user['email'] ?? \sprintf('%s@%s.pl', $username, \mb_strtolower(\str_replace(' ', '-', $fullname)));

            yield [
                'fullname' => $fullname,
                'username' => $username,
                'email' => $email,
                'super_admin' => $user['super_admin'] ?? $defaults['super_admin'] ?? false,
                'password' => $user['password'] ?? $defaults['password'] ?? $username,
                'enabled' => $user['enabled'] ?? $defaults['enabled'] ?? true,
                'avatar' => $user['avatar'] ?? $defaults['avatar'],
                'roles' => \array_unique(\array_merge($defaults['roles'], $user['roles'] ?? [])),
                'public' => $user['public'] ?? $defaults['public'],
            ];
        }
    }
}
