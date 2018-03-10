<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Security\User\UserInterface;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use DomainException;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * An image file.
 *
 * @see http://schema.org/ImageObject Documentation on Schema.org
 *
 * @Vich\Uploadable
 *
 * @ApiResource(iri="http://schema.org/ImageObject",
 *     attributes={
 *         "normalization_context": {"groups": {"ImageRead"}},
 *         "denormalization_context": {"groups": {"ImageWrite"}},
 *     },
 *     collectionOperations={
 *         "get": {
 *             "method": "GET",
 *         },
 *         "post": {
 *             "method": "POST",
 *             "access_control": "is_granted('ROLE_READER')",
 *         },
 *         "upload": {
 *             "route_name": "api_images_upload",
 *             "access_control": "is_granted('ROLE_READER')",
 *         },
 *     },
 *     itemOperations={
 *         "get": {
 *             "method": "GET",
 *         },
 *         "put": {
 *             "method": "PUT",
 *             "access_control": "is_granted('ROLE_ADMIN') or (user and object.isAuthor(user))",
 *         },
 *         "delete": {
 *             "method": "DELETE",
 *             "access_control": "is_granted('ROLE_ADMIN') or (user and object.isAuthor(user))",
 *         },
 *     })
 *
 *     @ORM\Entity
 *     @ORM\Table(name="images")
 */
class Image
{
    public const SUPPORTED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
    ];

    /**
     * @var Uuid
     *
     * @ORM\Id
     * @ORM\Column(type="uuid")
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     *
     * @Groups({"ImageRead"})
     */
    protected $id;

    /**
     * @ApiProperty(iri="http://schema.org/contentUrl")
     *
     * @var string actual bytes of the media object, for example the image file or video file
     *
     * @ORM\Column(type="string")
     *
     * @Assert\Url
     *
     * @Groups({"ImageRead", "ImageWrite", "ImageReadLess", "UserReadLess"})
     */
    protected $url;

    /**
     * @var int|null file size in bytes
     *
     * @ORM\Column(name="file_size", type="bigint", nullable=true)
     *
     * @ApiProperty(iri="http://schema.org/contentSize")
     *
     * @Groups({"ImageRead", "ImageWrite"})
     */
    protected $size;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true, nullable=true)
     */
    protected $fileName;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     *
     * @Groups({"ImageRead", "ImageWrite"})
     */
    protected $originalName;

    /**
     * @var File|null
     *
     * @Vich\UploadableField(mapping="images", fileNameProperty="fileName", originalName="originalName", size="size")
     */
    protected $file;

    /**
     * @var User|null The author of this content or rating. Please note that author is special in that HTML 5 provides a special mechanism for indicating authorship via the rel tag. That is equivalent to this and may be used interchangeably.
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     *
     * @Groups({"ImageRead", "ImageWrite"})
     */
    protected $author;

    /**
     * @var DateTime|null date when this media object was uploaded to this site
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Groups({"ImageRead"})
     */
    protected $uploadedAt;

    /**
     * @var DateTime|null the date on which the CreativeWork was most recently modified or when the item's entry was modified within a DataFeed
     *
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="update")
     *
     * @Groups({"ImageRead"})
     */
    protected $updatedAt;

    /**
     * @var DateTime|null the date on which the CreativeWork was created or the item was added to a DataFeed
     *
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
     *
     * @Groups({"ImageRead"})
     */
    protected $createdAt;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function updateUploadedAt(): void
    {
        $this->uploadedAt = new DateTime();
    }

    public function getUploadedAt(): ?DateTime
    {
        return $this->uploadedAt;
    }

    public function setFile(?File $image): void
    {
        $this->file = $image;
    }

    public function setSize(?int $size): void
    {
        $this->size = $size;
    }

    public function getSize(): ?int
    {
        return (int) $this->size;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFileName(?string $fileName): void
    {
        $this->fileName = $fileName;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function setOriginalName(?string $originalName): void
    {
        $this->originalName = $originalName;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(User $author): void
    {
        $this->author = $author;
    }

    public function isAuthor(?UserInterface $user): bool
    {
        $author = $this->getAuthor();

        return $author instanceof UserInterface && $author->isUser($user);
    }

    public function __toString(): string
    {
        return (string) $this->getUrl();
    }

    /**
     * @param \Symfony\Component\HttpFoundation\File\File $file
     * @param \App\Entity\User|null                       $author
     *
     * @throws \DomainException
     *
     * @return \App\Entity\Image
     */
    public static function fromFile(File $file, ?User $author = null): self
    {
        if (!\in_array($file->getMimeType(), self::SUPPORTED_MIME_TYPES, true)) {
            throw new DomainException(\sprintf('Give file mime type is not supported. Supported ones: %s', \implode(', ', self::SUPPORTED_MIME_TYPES)));
        }

        $image = new self();
        $image->file = $file;
        $image->author = $author;

        return $image;
    }
}
