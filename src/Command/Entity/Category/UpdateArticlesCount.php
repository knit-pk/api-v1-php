<?php

declare(strict_types=1);

namespace App\Command\Entity\Category;

use App\Entity\Article;
use App\Entity\Category;
use App\EntityProcessor\Handler\AbstractConsoleCommandEntityProcessorHandler;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

class UpdateArticlesCount extends AbstractConsoleCommandEntityProcessorHandler
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function handle(object $category): void
    {
        if (!$category instanceof Category) {
            throw new InvalidArgumentException('Expecting Category entity');
        }

        $category->setArticlesCount($this->getArticlesCount($category));
    }

    private function getArticlesCount(Category $category): int
    {
        $dql = \sprintf('SELECT COUNT(a.id) as count FROM %s a WHERE a.category = ?1', Article::class);

        return (int) $this->entityManager
            ->createQuery($dql)
            ->setParameter(1, $category->getId(), 'uuid')
            ->getResult()[0]['count'];
    }
}
