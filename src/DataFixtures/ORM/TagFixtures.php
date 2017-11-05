<?php
declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class TagFixtures extends Fixture
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->getTagsData() as ['name' => $name]) {
            $tag = new Tag();
            $tag->setName($name);

            $manager->persist($tag);
        }

        $manager->flush();
    }


    private function getTagsData(): array
    {
        return [
            ['name' => 'IT'],
            ['name' => 'Business'],
            ['name' => 'Computer Security'],
            ['name' => 'Programming'],
            ['name' => 'Development'],
            ['name' => 'Conference'],
            ['name' => 'Hackathon'],
            ['name' => 'Party'],
            ['name' => 'Meeting'],
            ['name' => 'Cracow'],
            ['name' => 'Poland'],
            ['name' => 'Free'],
            ['name' => 'Mobile'],
            ['name' => 'Web'],
        ];
    }
}