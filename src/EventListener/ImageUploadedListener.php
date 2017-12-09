<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Image;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RequestContextAwareInterface;
use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Storage\StorageInterface;

class ImageUploadedListener
{
    private $storage;
    private $requestStack;
    private $requestContextAware;

    public function __construct(StorageInterface $storage, RequestStack $requestStack, RequestContextAwareInterface $requestContextAware)
    {
        $this->storage = $storage;
        $this->requestStack = $requestStack;
        $this->requestContextAware = $requestContextAware;
    }

    public function onVichUploaderPostUpload(Event $event): void
    {
        $entity = $event->getObject();
        if ($entity instanceof Image) {
            $path = $this->storage->resolveUri($entity, 'file');
            $entity->setUrl($this->getUriForPath($path));
        }
    }

    private function getUriForPath(string $path): string
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request instanceof Request) {
            return $request->getUriForPath($path);
        }

        // Guess uri for console context
        return $this->guessUriForPath($path);
    }

    private function guessUriForPath(string $path): string
    {
        $context = $this->requestContextAware->getContext();

        $realPath = ltrim(sprintf('%s/%s', $context->getBaseUrl(), $path), '\/');

        return rtrim(sprintf('%s://%s/%s', $context->getScheme(), $context->getHost(), $realPath), '\/');
    }
}
