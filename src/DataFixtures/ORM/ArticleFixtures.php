<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\CommentReply;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Generator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class ArticleFixtures extends Fixture implements DependentFixtureInterface
{
    private const ARTICLE_FIXTURES = __DIR__.'/../Resources/articles';

    private $connection;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->connection = $entityManager->getConnection();
    }

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     *
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function load(ObjectManager $manager): void
    {
        $this->createTriggers();

        // Predefined articles
        foreach ($this->getArticleFixtures() as $fixture) {
            $article = new Article();
            if (isset($fixture['image'])) {
                /** @var \App\Entity\Image $image */
                $image = $this->getReference($fixture['image']);
                $article->setImage($image);
            }
            $article->setTitle($fixture['title']);
            /** @var \App\Entity\Category $category */
            $category = $this->getReference($fixture['category']);
            $article->setCode(\sprintf('%s/%s/%s', $category->getCode(), \mb_strtolower(Urlizer::transliterate($article->getTitle(), '-')), \mb_substr((string) $article->getId(), 0, 6)));

            /** @var \App\Entity\User $author */
            $author = $this->getReference($fixture['author']);
            $article->setAuthor($author);

            $article->setCategory($category);
            $article->setContent($this->parseContent($fixture['content']));
            $article->setDescription($fixture['description']);
            $article->setPublished(true === $fixture['published']);

            /** @var string[] $tags */
            $tags = $fixture['tags'];
            foreach ($tags as $tagReference) {
                /** @var \App\Entity\Tag $tag */
                $tag = $this->getReference($tagReference);
                $article->addTag($tag);
            }

            if (isset($fixture['comments'])) {
                /** @var array[] $comments */
                $comments = $fixture['comments'];
                foreach ($comments as $commentFixture) {
                    $comment = new Comment();
                    /** @var \App\Entity\User $commentAuthor */
                    $commentAuthor = $this->getReference($commentFixture['author']);
                    $comment->setAuthor($commentAuthor);
                    $comment->setText($commentFixture['text']);
                    $comment->setArticle($article);

                    if (isset($commentFixture['replies'])) {
                        /** @var array[] $replies */
                        $replies = $commentFixture['replies'];
                        foreach ($replies as $replyData) {
                            $reply = new CommentReply();
                            /** @var \App\Entity\User $replyAuthor */
                            $replyAuthor = $this->getReference($replyData['author']);
                            $reply->setText($replyData['text']);
                            $reply->setAuthor($replyAuthor);
                            $reply->setComment($comment);

                            $manager->persist($reply);
                        }
                    }

                    $manager->persist($comment);
                }
            }

            $manager->persist($article);
        }

        $manager->flush();
    }

    /**
     * @throws \RuntimeException
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     *
     * @return Generator|array[]
     */
    private function getArticleFixtures(): Generator
    {
        $finder = Finder::create()
            ->in(self::ARTICLE_FIXTURES)
            ->name('*.yaml');

        foreach ($finder->getIterator() as $yamlFile) {
            yield Yaml::parse($yamlFile->getContents()) + [
                'published' => false,
                ];
        }
    }

    /**
     * @param string $content
     *
     * @return string
     */
    private function parseContent(string $content): string
    {
        return \preg_replace_callback('~\`?\$\$ref-([\w\-\.\_]+)\`?~', function (array $matches) {
            $fullMatch = $matches[0];

            if ('`' === $fullMatch[0] && '`' === $fullMatch[-1]) {
                return \mb_substr($fullMatch, 1, -1);
            }

            $reference = $matches[1];
            if ($this->hasReference($reference)) {
                return (string) $this->getReference($reference);
            }

            return $fullMatch;
        }, $content);
    }

    /**
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Doctrine\DBAL\DBALException
     */
    private function createTriggers(): void
    {
        $driverName = $this->connection->getDriver()->getName();
        $sqlName = 'create_comment_triggers';
        $sqlFile = \sprintf('%s/../Resources/sql/%s/%s.sql', __DIR__, $driverName, $sqlName);

        if (!\is_file($sqlFile)) {
            dump(\sprintf('Notice: SQL File %s could not be found for driver %s', $sqlName, $driverName));

            return;
        }

        $this->connection->exec(\file_get_contents($sqlFile));
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
