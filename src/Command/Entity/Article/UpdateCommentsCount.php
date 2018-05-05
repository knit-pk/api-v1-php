<?php

declare(strict_types=1);

namespace App\Command\Entity\Article;

use App\Entity\Article;
use App\Entity\Comment;
use App\EntityBatchProcessor\Handler\AbstractConsoleCommandBatchProcessorHandler;
use InvalidArgumentException;

final class UpdateCommentsCount extends AbstractConsoleCommandBatchProcessorHandler
{
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

        $commentsCount = 0;

        /** @var Comment $comment */
        foreach ($entity->getComments() as $comment) {
            $commentsCount += $comment->getReplies()->count();
        }

        $entity->setCommentsCount($commentsCount);
    }
}
