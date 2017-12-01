<?php
declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\UserInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @Vich\Uploadable()
 *
 * @ApiResource(attributes={
 *      "normalization_context"={"groups"={"ImageRead"}},
 *      "denormalization_context"={"groups"={"ImageWrite"}},
 * },
 * collectionOperations={
 *     "get"={
 *          "method"="GET",
 *     },
 *     "post"={
 *          "method"="POST",
 *          "access_control"="is_granted('ROLE_USER_WRITER')",
 *     },
 *     "upload"={
 *          "route_name"="api_images_upload",
 *          "access_control"="is_granted('ROLE_USER_WRITER')",
 *      },
 * },
 * itemOperations={
 *     "get"={
 *          "method"="GET",
 *     },
 *     "put"={
 *          "method"="PUT",
 *          "access_control"="is_granted('ROLE_ADMIN') or (user and object.isAuthor(user))",
 *     },
 *     "delete"={
 *          "method"="DELETE",
 *          "access_control"="is_granted('ROLE_ADMIN') or (user and object.isAuthor(user))",
 *     },
 * })
 *
 * @ORM\Entity()
 * @ORM\Table(name="images")
 */
class Image
{
    /**
     * @var Uuid
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid")
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     *
     * @Groups({"ImageRead","ImageReadLess"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     *
     * @Groups({"ImageRead","ImageWrite","ImageReadLess","UserReadLess"})
     */
    protected $url;

    /**
     * @var string
     *
     * @ORM\Column(type="string",unique=true,nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string",nullable=true)
     *
     * @Groups({"ImageRead","ImageWrite"})
     */
    private $originalName;

    /**
     * @var File|null
     *
     * @Vich\UploadableField(mapping="images",fileNameProperty="name",originalName="originalName")
     */
    private $file;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="author_id",referencedColumnName="id",onDelete="CASCADE")
     *
     * @Assert\NotBlank()
     *
     * @Groups({"ImageRead","ImageWrite"})
     */
    protected $author;

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


    public function setFile(?File $image = null): void
    {
        $this->file = $image;
    }


    public function getFile(): ?File
    {
        return $this->file;
    }


    public function setName(?string $name): void
    {
        $this->name = $name;
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


    public function getName(): ?string
    {
        return $this->name;
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

        if (!$author instanceof User) {
            return false;
        }

        return $author->isUser($user);
    }


    public function __toString(): string
    {
        return (string) $this->getUrl();
    }
}