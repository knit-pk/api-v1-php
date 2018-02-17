<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Image;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Generator;
use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageFixtures extends Fixture
{
    private const APP_ROOT = __DIR__.'/../../../';
    private const IMAGE_FIXTURES = __DIR__.'/../Resources/images';

    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     *
     * @throws \DomainException
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

        foreach ($this->getImagesData() as $data) {
            $defaultAvatarImage = new SplFileInfo($data['file']);
            if ($defaultAvatarImage->isFile()) {
                $this->filesystem->copy($defaultAvatarImage->getRealPath(), $tempFile, true);

                $file = new UploadedFile($tempFile, $data['name'], null, null, null, true);
                $avatar = Image::fromFile($file);

                $manager->persist($avatar);

                $this->addReference(\sprintf('image-%s', $data['name']), $avatar);
            }
        }

        $manager->flush();
    }

    /**
     * @throws \LogicException
     * @throws \InvalidArgumentException
     *
     * @return \Generator|array[]
     */
    private function getImagesData(): Generator
    {
        $finder = Finder::create()
            ->in(self::IMAGE_FIXTURES)
            ->name('*.{png,jpg}');

        foreach ($finder->getIterator() as $imageFile) {
            yield [
                'file' => $imageFile->getRealPath(),
                'name' => $imageFile->getFilename(),
            ];
        }
    }
}
