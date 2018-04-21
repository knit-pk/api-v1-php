<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(attributes={
 *     "filters": {"app.category.search_filter", "app.category.group_filter"},
 *     "normalization_context": {"groups": {"CategoryRead", "MetadataRead"}},
 *     "denormalization_context": {"groups": {"CategoryWrite", "MetadataWrite"}},
 * },
 * collectionOperations={
 *     "get": {
 *         "method": "GET",
 *     },
 *     "post": {
 *         "access_control": "is_granted('ROLE_READER')",
 *         "method": "POST",
 *     },
 * },
 * itemOperations={
 *     "get": {
 *         "method": "GET",
 *     },
 *     "put": {
 *         "method": "PUT",
 *         "access_control": "is_granted('ROLE_ADMIN')",
 *     },
 *     "delete": {
 *         "method": "DELETE",
 *         "access_control": "is_granted('ROLE_ADMIN')",
 *     },
 * })
 *
 * @ORM\Entity
 * @ORM\Table(name="categories")
 */
class Category
{
    /**
     * @var \Ramsey\Uuid\UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid")
     *
     * @Groups({"CategoryRead"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true)
     *
     * @Gedmo\Slug(fields={"name"}, separator="-", updatable=true, unique=true)
     *
     * @Groups({"CategoryRead"})
     */
    protected $code;

    /**
     * @var string the name of the category
     *
     * @ORM\Column(type="string", nullable=false)
     *
     * @ApiProperty(iri="http://schema.org/name")
     *
     * @Assert\NotBlank
     * @Assert\Length(min="3", max="100")
     *
     * @Groups({"CategoryRead", "CategoryWrite"})
     */
    protected $name;

    /**
     * @var string
     *
     * @Assert\NotBlank
     *
     * @Groups({"CategoryRead", "CategoryWrite"})
     */
    protected $description;

    /**
     * @var Metadata
     *
     * @ORM\Embedded(class=Metadata::class)
     *
     * @Groups({"CategoryRead", "CategoryWrite"})
     */
    protected $metadata;

    /**
     * @ApiProperty(iri="http://schema.org/image")
     *
     * @var Image
     *
     * @ORM\ManyToOne(targetEntity="Image")
     * @ORM\JoinColumn(name="category_image_id", referencedColumnName="id", onDelete="RESTRICT")
     *
     * @Assert\NotBlank
     *
     * @Groups({"CategoryRead", "CategoryWrite"})
     */
    protected $image;

    /**
     * @var int Aggregate field that contains total number of articles in category
     *
     * @ORM\Column(type="integer", options={"unsigned": true})
     *
     * @Assert\GreaterThanOrEqual(value=0)
     *
     * @Groups({"CategoryRead", "CategoryAdminUpdate"})
     */
    protected $articlesCount;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->articlesCount = 0;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setMetadata(Metadata $metadata): void
    {
        $this->metadata = $metadata;
    }

    public function getImage(): Image
    {
        return $this->image;
    }

    public function setImage(Image $image): void
    {
        $this->image = $image;
    }

    public function incrementArticlesCount(): void
    {
        ++$this->articlesCount;
    }

    public function decrementArticlesCount(): void
    {
        --$this->articlesCount;
    }

    public function getArticlesCount(): int
    {
        return $this->articlesCount;
    }

    public function setArticlesCount(int $articlesCount): void
    {
        $this->articlesCount = $articlesCount;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
