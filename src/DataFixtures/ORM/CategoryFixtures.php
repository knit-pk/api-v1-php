<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Generator;
use Symfony\Component\Yaml\Yaml;

class CategoryFixtures extends Fixture
{
    private const CATEGORY_FIXTURES = __DIR__.'/../Resources/fixtures/categories.yaml';

    public const PUBLIC_CATEGORY_CODES = [
        'news',
        'article',
        'announcement',
    ];

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     *
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     * @throws \Doctrine\Common\DataFixtures\BadMethodCallException
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->getCategoryFixtures() as $fixture) {
            $category = new Category();
            $category->setName($fixture['name']);

            if ($fixture['public']) {
                $code = \str_replace(' ', '-', \mb_strtolower($fixture['name']));
                $this->addReference(\sprintf('category-%s', $code), $category);
            }

            $manager->persist($category);
        }

        $manager->flush();
    }

    /**
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     *
     * @return \Generator
     */
    private function getCategoryFixtures(): Generator
    {
        $fixtures = Yaml::parseFile(self::CATEGORY_FIXTURES);

        $defaults = $fixtures['_defaults'];

        /** @var array[] $categories */
        $categories = $fixtures['categories'];
        foreach ($categories as $category) {
            yield [
                'name' => $category['name'],
                'public' => $category['public'] ?? $defaults['public'],
            ];
        }
    }
}
