<?php
declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->getCategoriesData() as ['name' => $name]) {
            $category = new Category();
            $category->setName($name);

            $manager->persist($category);
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