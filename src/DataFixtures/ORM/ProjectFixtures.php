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
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $projectAuthor = new User();
        $projectAuthor->setFullname('Projects Author');
        $projectAuthor->setUsername('author');
        $projectAuthor->setEmail('author@author.pl');
        $projectAuthor->setEnabled(true);
        $projectAuthor->setRoles(['ROLE_USER']);
        $projectAuthor->setPlainPassword('author');

        $manager->persist($projectAuthor);

        for($i = 1; $i <= 10; ++$i) {
            $projectName = sprintf('Project %d', $i);

            $project = new Project();
            $project->setName($projectName);
            $project->setDescription(sprintf('Fantastic %s description.', $projectName));
            $project->setAuthor($projectAuthor);
            $project->setUrl(sprintf('https://github.com/%s/%s', strtolower($projectAuthor->getUsername()), strtolower(str_replace(' ', '-', $projectName))));
            $manager->persist($project);
        }

        $manager->flush();
    }
}