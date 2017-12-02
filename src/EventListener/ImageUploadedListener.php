<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Image;
use DomainException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Storage\StorageInterface;

class ImageUploadedListener
{
    private $storage;
    private $requestStack;

    public function __construct(StorageInterface $storage, RequestStack $requestStack)
    {
        $this->storage = $storage;
        $this->requestStack = $requestStack;
    }

    public function onVichUploaderPostUpload(Event $event): void
    {
        $entity = $event->getObject();
        if ($entity instanceof Image) {
            $path = $this->storage->resolveUri($entity, 'file');
            $entity->setUrl($this->getRequest()->getUriForPath($path));
        }
    }

    private function getRequest(): Request
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            throw new DomainException('Request object is required for listener');
        }

        return $request;
    }
}
