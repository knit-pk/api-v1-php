<?php
declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Article;
use App\Entity\Category;
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
        $author = new User();
        $author->setFullname('Articles Author');
        $author->setUsername('author-a');
        $author->setEmail('author-a@author.pl');
        $author->setEnabled(true);
        $author->setRoles(['ROLE_USER']);
        $author->setPlainPassword('author-a');

        $manager->persist($author);

        $category = new Category();
        $category->setName('Test');

        $manager->persist($category);

        for ($i = 1; $i <= 10; ++$i) {
            $articleTitle = sprintf('Article %d', $i);

            $article = new Article();
            $article->setTitle($articleTitle);
            $article->setContent(sprintf('Awesome short %s about.', $articleTitle));
            $article->setDescription(sprintf('Awesome %s content.', $articleTitle));
            $article->setAuthor($author);
            $article->setCategory($category);

            $manager->persist($article);
        }

        $manager->flush();
    }
}