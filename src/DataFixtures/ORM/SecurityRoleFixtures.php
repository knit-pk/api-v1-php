<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\SecurityRole;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Generator;
use Symfony\Component\Yaml\Yaml;

class SecurityRoleFixtures extends Fixture
{
    private const SECURITY_ROLE_FIXTURES = __DIR__.'/../Resources/fixtures/security_roles.yaml';

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     *
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->getSecurityRoleFixtures() as $fixture) {
            $securityRole = new SecurityRole($fixture['role']);
            $securityRole->setName($fixture['name']);

            if ($fixture['public']) {
                $code = \str_replace(' ', '-', \mb_strtolower($fixture['role']));
                $this->addReference(\sprintf('security-%s', $code), $securityRole);
            }

            if (User::ROLE_DEFAULT === $securityRole->getRole()) {
                $this->addReference('security-role_default', $securityRole);
            }

            $manager->persist($securityRole);
        }

        $manager->flush();
    }

    /**
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     *
     * @return \Generator
     */
    private function getSecurityRoleFixtures(): Generator
    {
        $fixtures = Yaml::parseFile(self::SECURITY_ROLE_FIXTURES);

        $defaults = $fixtures['_defaults'];

        /** @var array[] $securityRoles */
        $securityRoles = $fixtures['security_roles'];
        foreach ($securityRoles as $securityRole) {
            yield [
                'name' => $securityRole['name'],
                'role' => $securityRole['role'],
                'public' => $securityRole['public'] ?? $defaults['public'],
            ];
        }
    }
}
