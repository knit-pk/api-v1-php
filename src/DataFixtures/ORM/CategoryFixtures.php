<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Category;
use App\Entity\Image;
use App\Entity\Metadata;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Generator;
use Symfony\Component\Yaml\Yaml;

class CategoryFixtures extends Fixture implements DependentFixtureInterface
{
    private const CATEGORY_FIXTURES = __DIR__.'/../Resources/fixtures/categories.yaml';

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->getCategoryFixtures() as $fixture) {
            $category = new Category();
            $category->setName($fixture['name']);
            $category->setDescription($fixture['description']);
            $category->setOverlayColor($fixture['overlayColor']);
            $category->setMetadata(new Metadata($fixture['metadata']['title'], $fixture['metadata']['description']));

            /** @var Image $image */
            $image = $this->getReference($fixture['image']);
            $category->setImage($image);

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
                'description' => $category['description'] ?? $defaults['description'],
                'metadata' => $category['metadata'] ?? $defaults['metadata'],
                'overlayColor' => $category['overlayColor'] ?? $defaults['overlayColor'],
                'image' => $category['image'] ?? $defaults['image'],
                'public' => $category['public'] ?? $defaults['public'],
            ];
        }
    }

    public function getDependencies(): array
    {
        return [
            ImageFixtures::class,
        ];
    }
}
