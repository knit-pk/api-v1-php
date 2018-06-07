<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\CommentReply;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CommentReplyEventSubscriber implements EventSubscriberInterface
{
    public function updateCommentReplies(GetResponseForControllerResultEvent $event): void
    {
        $commentReply = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$commentReply instanceof CommentReply) {
            return;
        }

        if ('DELETE' === $method) {
            $commentReply->getComment()->removeReply($commentReply);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['updateCommentReplies', EventPriorities::PRE_WRITE],
        ];
    }
}
