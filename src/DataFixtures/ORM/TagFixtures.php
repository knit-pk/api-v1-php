<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Generator;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Yaml\Yaml;

class TagFixtures extends Fixture
{
    private const TAG_FIXTURES = __DIR__.'/../Resources/fixtures/tags.yaml';

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     *
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->getTagFixtures() as $fixture) {
            $tag = new Tag(Uuid::uuid4());
            $tag->setName($fixture['name']);

            if ($fixture['public']) {
                $code = \str_replace(' ', '-', \mb_strtolower($fixture['name']));
                $this->addReference(\sprintf('tag-%s', $code), $tag);
            }

            $manager->persist($tag);
        }

        $manager->flush();
    }

    /**
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     *
     * @return \Generator
     */
    private function getTagFixtures(): Generator
    {
        $fixtures = Yaml::parseFile(self::TAG_FIXTURES);

        $defaults = $fixtures['_defaults'];

        /** @var array[] $tags */
        $tags = $fixtures['tags'];
        foreach ($tags as $tag) {
            yield [
                'name' => $tag['name'],
                'public' => $tag['public'] ?? $defaults['public'],
            ];
        }
    }
}
