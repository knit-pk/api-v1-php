<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Project;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class ProjectFixtures extends Fixture
{
    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        /** @var User $author */
        $author = $this->getReference('user-reader');

        for ($i = 1; $i <= 10; ++$i) {
            $name = sprintf('Project %d', $i);

            $project = new Project();
            $project->setName($name);
            $project->setDescription(sprintf('Fantastic %s description.', $name));
            $project->setAuthor($author);
            $project->setUrl(sprintf('https://github.com/%s/%s', strtolower($author->getUsername()), strtolower(str_replace(' ', '-', $name))));

            $manager->persist($project);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
