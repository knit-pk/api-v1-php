<?php

declare(strict_types=1);

namespace App\Action\Image;

use App\Entity\Image;
use App\Security\UserProvider\UserEntityProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

final class ImageUploadAction
{
    private $userEntityProvider;

    public function __construct(UserEntityProvider $userEntityProvider)
    {
        $this->userEntityProvider = $userEntityProvider;
    }

    /**
     * @Route(name="api_images_upload",
     *     path="/images/upload",
     *     defaults={
     *         "_api_receive": false,
     *         "_api_resource_class": Image::class,
     *         "_api_collection_operation_name": "upload"
     *     },
     * )
     * @Method("POST")
     *
     * @param \Symfony\Component\HttpFoundation\Request           $request
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \App\Security\Exception\SecurityException
     * @throws \DomainException
     *
     * @return \App\Entity\Image
     */
    public function __invoke(Request $request, UserInterface $user): Image
    {
        $imageFile = $request->files->get('image');

        if (!$imageFile instanceof UploadedFile) {
            throw new HttpException(400, 'Field `image` is required, and must be a file');
        }

        return Image::fromFile($imageFile, $this->userEntityProvider->getReference($user));
    }
}
