<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Comment;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CommentEventSubscriber implements EventSubscriberInterface
{
    public function updateComments(GetResponseForControllerResultEvent $event): void
    {
        $comment = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$comment instanceof Comment) {
            return;
        }

        if ('DELETE' === $method) {
            $comment->getArticle()->removeComment($comment);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['updateComments', EventPriorities::PRE_WRITE],
        ];
    }
}
