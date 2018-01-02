<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class TagFixtures extends Fixture
{
    public const PUBLIC_TAG_CODES = [
        'it',
        'university',
        'fun',
        'programming',
        'poland',
    ];

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $publicTags = array_flip(self::PUBLIC_TAG_CODES);

        foreach ($this->getTagsData() as ['name' => $name]) {
            $tag = new Tag();
            $tag->setName($name);

            $code = str_replace(' ', '-', strtolower($name));
            if (isset($publicTags[$code])) {
                $this->addReference(sprintf('tag-%s', $code), $tag);
            }

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
            ['name' => 'University'],
            ['name' => 'Fun'],
        ];
    }
}
