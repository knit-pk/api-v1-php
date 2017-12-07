<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Article;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class ArticleFixtures extends Fixture
{
    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $categoryCollection = CategoryFixtures::PUBLIC_CATEGORY_CODES;
        $usersCollection = UserFixtures::PUBLIC_USERNAMES;
        $tagCollection = TagFixtures::PUBLIC_TAG_CODES;
        $tagCollectionTotalItems = \count($tagCollection);

        /** @var \App\Entity\Image $image */
        $image = $this->getReference('image-card-photo-4.jpg');

        for ($i = 1; $i <= 10; ++$i) {
            shuffle($usersCollection);
            /** @var \App\Entity\User $author */
            $author = $this->getReference(sprintf('user-%s', $usersCollection[0]));

            shuffle($categoryCollection);
            /** @var \App\Entity\Category $category */
            $category = $this->getReference(sprintf('category-%s', $categoryCollection[0]));

            $articleTitle = sprintf('Article %d', $i);

            $article = new Article();
            $article->setTitle($articleTitle);
            $article->setContent(sprintf('Awesome short %s about.', $articleTitle));
            $article->setDescription(sprintf('Awesome %s content.', $articleTitle));
            $article->setAuthor($author);
            $article->setCategory($category);
            $article->setImage($image);

            // Add random number of random tags
            shuffle($tagCollection);
            $tagsPerArticle = random_int(1, $tagCollectionTotalItems);
            foreach ($tagCollection as $randomTag) {
                /** @var \App\Entity\Tag $tag */
                $tag = $this->getReference(sprintf('tag-%s', $randomTag));
                $article->addTag($tag);
                if (0 === --$tagsPerArticle) {
                    break;
                }
            }

            $manager->persist($article);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TagFixtures::class,
            CategoryFixtures::class,
            UserFixtures::class,
            ImageFixtures::class,
        ];
    }
}
