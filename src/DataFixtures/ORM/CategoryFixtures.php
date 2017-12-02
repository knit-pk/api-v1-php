<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
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
     * @throws \Doctrine\Common\DataFixtures\BadMethodCallException
     */
    public function load(ObjectManager $manager): void
    {
        $publicCategories = array_flip(self::PUBLIC_CATEGORY_CODES);

        foreach ($this->getCategoriesData() as ['name' => $name]) {
            $category = new Category();
            $category->setName($name);

            $manager->persist($category);

            $code = str_replace(' ', '-', strtolower($name));
            if (isset($publicCategories[$code])) {
                $this->addReference(sprintf('category-%s', $code), $category);
            }
        }

        $manager->flush();
    }

    private function getCategoriesData(): array
    {
        return [
            ['name' => 'News'],
            ['name' => 'Article'],
            ['name' => 'Announcement'],
        ];
    }
}
