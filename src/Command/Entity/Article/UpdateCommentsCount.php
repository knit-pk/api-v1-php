<?php

declare(strict_types=1);

namespace App\Command\Entity\Article;

use App\Entity\Article;
use App\Entity\Comment;
use App\EntityProcessor\Handler\AbstractConsoleCommandEntityProcessorHandler;
use InvalidArgumentException;

final class UpdateCommentsCount extends AbstractConsoleCommandEntityProcessorHandler
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
            $commentsCount += 1 + $comment->getReplies()->count();
        }

        $entity->setCommentsCount($commentsCount);
    }
}
