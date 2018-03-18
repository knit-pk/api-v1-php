<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Image;
use App\UrlGenerator\UrlGeneratorInterface;
use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Storage\StorageInterface;

final class ImageUploadedListener
{
    private $storage;
    private $urlGenerator;

    public function __construct(StorageInterface $storage, UrlGeneratorInterface $urlGenerator)
    {
        $this->storage = $storage;
        $this->urlGenerator = $urlGenerator;
    }

    public function onVichUploaderPostUpload(Event $event): void
    {
        $entity = $event->getObject();
        if ($entity instanceof Image) {
            $path = $this->storage->resolveUri($entity, 'file');
            $entity->setUrl($this->urlGenerator->generate($path));
            $entity->updateUploadedAt();
        }
    }
}
