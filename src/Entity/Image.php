<?php
declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @Vich\Uploadable()
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
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
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
     */
    private $originalName;

    /**
     * @var File|null
     *
     * @Vich\UploadableField(mapping="images",fileNameProperty="name",originalName="originalName")
     */
    private $file;

    /**
     * @var DateTime|null the date on which the CreativeWork was most recently modified or when the item's entry was modified within a DataFeed
     *
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="update")
     */
    protected $updatedAt;

    /**
     * @var DateTime|null the date on which the CreativeWork was created or the item was added to a DataFeed
     *
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
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
}