<?php
declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class ArticleFixtures extends Fixture
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $projectAuthor = new User();
        $projectAuthor->setFullname('Articles Author');
        $projectAuthor->setUsername('author-a');
        $projectAuthor->setEmail('author-a@author.pl');
        $projectAuthor->setEnabled(true);
        $projectAuthor->setRoles(['ROLE_USER']);
        $projectAuthor->setPlainPassword('author-a');

        $manager->persist($projectAuthor);

        for ($i = 1; $i <= 10; ++$i) {
            $articleTitle = sprintf('Article %d', $i);

            $article = new Article();
            $article->setTitle($articleTitle);
            $article->setBody(sprintf('Awesome short %s about.', $articleTitle));
            $article->setAbout(sprintf('Awesome %s content.', $articleTitle));
            $article->setAuthor($projectAuthor);
            for ($j = 1; $j <= 10 % $i; ++$j) {
                $article->addSection('category-' . $j);
            }

            $manager->persist($article);
        }

        $manager->flush();
    }
}