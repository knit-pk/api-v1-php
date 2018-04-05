<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Article;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ArticleEventSubscriber implements EventSubscriberInterface
{
    public function updateSlug(GetResponseForControllerResultEvent $event): void
    {
        $article = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$article instanceof Article || !\in_array($method, ['POST', 'PUT'], true)) {
            return;
        }

        $article->setCode(\sprintf('%s/%s/%s', $article->getCategory()->getCode(), \mb_strtolower(Urlizer::transliterate($article->getTitle(), '-')), \mb_substr((string) $article->getId(), 0, 6)));
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['updateSlug', EventPriorities::PRE_WRITE],
        ];
    }
}
