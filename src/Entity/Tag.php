<?php
declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(attributes={
 *      "filters"={"tag.search_filter"},
 *      "normalization_context"={"groups"={"TagRead"}},
 *      "denormalization_context"={"groups"={"TagWrite"}},
 * },
 * collectionOperations={
 *     "get"={
 *          "method"="GET",
 *     },
 *     "post"={
 *          "access_control"="is_granted('ROLE_READER')",
 *          "method"="POST",
 *     },
 * },
 * itemOperations={
 *     "get"={
 *          "method"="GET",
 *     },
 *     "put"={
 *          "method"="PUT",
 *          "access_control"="is_granted('ROLE_ADMIN')",
 *     },
 *     "delete"={
 *          "method"="DELETE",
 *          "access_control"="is_granted('ROLE_ADMIN')",
 *     },
 * })
 *
 * @ORM\Entity()
 * @ORM\Table(name="tags")
 */
class Tag
{
    /**
     * @var Uuid
     *
     * @ORM\Id
     * @ORM\Column(type="uuid")
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     *
     * @Groups({"TagRead"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true)
     *
     * @Gedmo\Slug(fields={"name"},separator="-",updatable=true,unique=true)
     *
     * @Groups({"TagRead"})
     */
    protected $code;

    /**
     * @var null|string the name of the tag
     *
     * @ORM\Column(type="string", nullable=false)
     *
     * @ApiProperty(iri="http://schema.org/name")
     *
     * @Assert\NotBlank()
     * @Assert\Length(min="3",max="100")
     *
     * @Groups({"TagRead","TagWrite"})
     */
    protected $name;


    /**
     * @return null|Uuid
     */
    public function getId(): ?Uuid
    {
        return $this->id;
    }


    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }


    /**
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }


    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }


    public function __toString(): string
    {
        return (string) $this->name;
    }

}