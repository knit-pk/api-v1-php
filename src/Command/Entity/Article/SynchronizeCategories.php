<?php

declare(strict_types=1);

namespace App\Command\Entity\Article;

use App\Entity\Article;
use App\EntityBatchProcessor\Handler\AbstractConsoleCommandBatchProcessorHandler;
use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;

final class SynchronizeCategories extends AbstractConsoleCommandBatchProcessorHandler
{
    private $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function handle(object $entity): void
    {
        if (!$entity instanceof Article) {
            throw new InvalidArgumentException('Expecting Article entity');
        }

        $category = $entity->getCategory();
        if (!$this->categories->contains($category)) {
            $category->setArticlesCount(0);
            $this->categories->add($category);
        }

        $category->incrementArticlesCount();
    }
}
