<?php
declare(strict_types=1);

namespace App\Action\Image;

use App\Entity\Image;
use App\Entity\User;
use DomainException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class ImageUploadAction
{
    private const SUPPORTED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
    ];


    /**
     * @Route(name="api_images_upload",
     *      path="/images/upload",
     *      defaults={
     *          "_api_receive"=false,
     *          "_api_resource_class"=Image::class,
     *          "_api_collection_operation_name"="upload"
     *      },
     * )
     * @Method("POST")
     *
     * @param \Symfony\Component\HttpFoundation\Request           $request
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     *
     * @return \App\Entity\Image
     */
    public function __invoke(Request $request, UserInterface $user)
    {

        $imageFile = $request->files->get('image');

        if (!$imageFile instanceof UploadedFile) {
            throw new DomainException('Image file is required.');
        }

        if (!\in_array($imageFile->getMimeType(), self::SUPPORTED_MIME_TYPES, true)) {
            throw new DomainException(sprintf('Uploaded file must be an image. Supported mime types: %s', implode(', ', self::SUPPORTED_MIME_TYPES)));
        }

        $image = new Image();
        $image->setFile($imageFile);
        $image->setAuthor($user);

        return $image;
    }
}