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
        $projectAuthor->setFullname('Project Author');
        $projectAuthor->setUsername('author');
        $projectAuthor->setEmail('author@author.pl');
        $projectAuthor->setEnabled(true);
        $projectAuthor->setRoles(['ROLE_USER']);
        $projectAuthor->setPlainPassword('author');
        $manager->persist($projectAuthor);


        $project = new Project();
        $project->setName('Project');
        $project->setDescription('Fantastic project description');
        $project->setUrl('http://github.com/author/project');
        $project->setAuthor($projectAuthor);
        $manager->persist($project);

        $manager->flush();
    }
}