<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Image;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Generator;
use Ramsey\Uuid\Uuid;
use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Yaml\Yaml;

class ImageFixtures extends Fixture
{
    private const APP_ROOT = __DIR__.'/../../../';
    private const IMAGE_FILE_FIXTURES = __DIR__.'/../Resources/images';
    private const IMAGE_FIXTURES = __DIR__.'/../Resources/fixtures/images.yaml';

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
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     * @throws \DomainException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     * @throws \Symfony\Component\Filesystem\Exception\FileNotFoundException
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     */
    public function load(ObjectManager $manager): void
    {
        $tempFile = self::APP_ROOT.'var/temp.file';

        foreach ($this->getImageFixtures() as $fixture) {
            $imageFile = $fixture['file'];
            if ($imageFile instanceof SplFileInfo) {
                $this->filesystem->copy($imageFile->getRealPath(), $tempFile, true);

                $file = new UploadedFile($tempFile, $fixture['name'], null, null, true);
                $image = Image::fromFile(Uuid::uuid4(), $file);
            } else {
                $image = new Image(Uuid::uuid4());
                $image->setOriginalName($fixture['name']);
                $image->setUrl($fixture['url']);
            }

            if ($fixture['public']) {
                $this->addReference(\sprintf('image-%s', $fixture['name']), $image);
            }

            $manager->persist($image);
        }

        $manager->flush();
    }

    /**
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     *
     * @return \Generator|array[]
     */
    private function getImageFixtures(): Generator
    {
        $finder = Finder::create()
            ->in(self::IMAGE_FILE_FIXTURES)
            ->name('*.{png,jpg,jpeg}');

        foreach ($finder->getIterator() as $imageFile) {
            yield [
                'file' => $imageFile,
                'name' => $imageFile->getFilename(),
                'public' => true,
            ];
        }

        $fixtures = Yaml::parseFile(self::IMAGE_FIXTURES);
        $defaults = $fixtures['_defaults'];

        /** @var array[] $images */
        $images = $fixtures['images'];
        foreach ($images as $image) {
            yield [
                'file' => null,
                'name' => $image['name'],
                'url' => $image['url'],
                'public' => $image['public'] ?? $defaults['public'],
            ];
        }
    }
}
