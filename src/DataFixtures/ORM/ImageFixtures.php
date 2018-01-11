<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Image;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use SplFileInfo;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageFixtures extends Fixture
{
    private const APP_ROOT = __DIR__.'/../../../';

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     * @throws \Symfony\Component\Filesystem\Exception\FileNotFoundException
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     * @throws \Doctrine\Common\DataFixtures\BadMethodCallException
     */
    public function load(ObjectManager $manager): void
    {
        $tempFile = self::APP_ROOT.'var/temp.file';
        $fs = $this->container->get('filesystem');

        foreach ($this->getImagesData() as $data) {
            $defaultAvatarImage = new SplFileInfo($data['file']);
            if ($defaultAvatarImage->isFile()) {
                $fs->copy($defaultAvatarImage->getRealPath(), $tempFile, true);

                $avatar = new Image();
                $file = new UploadedFile($tempFile, $data['name'], null, $defaultAvatarImage->getSize(), null, true);
                $avatar->setFile($file);

                $manager->persist($avatar);

                $this->addReference(\sprintf('image-%s', $data['name']), $avatar);
            }
        }

        $manager->flush();
    }

    private function getImagesData(): array
    {
        return [
            [
                'file' => self::APP_ROOT.'src/DataFixtures/Resources/images/avatar.png',
                'name' => 'avatar.png',
            ],
            [
                'file' => self::APP_ROOT.'src/DataFixtures/Resources/images/card-photo-4.jpg',
                'name' => 'card-photo-4.jpg',
            ],
        ];
    }
}
