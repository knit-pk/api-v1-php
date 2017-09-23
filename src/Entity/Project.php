<?php
declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\UserInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(attributes={
 *   "access_control"="is_granted('ROLE_READER')",
 * },
 * collectionOperations={
 *     "get"={
 *          "method"="GET",
 *          "swagger_context"={
 *               "parameters"={
 *                   {
 *                       "name"="Authorization",
 *                       "description"="Access token",
 *                       "in"="header",
 *                       "default"="Bearer {{token}}",
 *                       "required"=true,
 *                       "type"="string",
 *                   },
 *               },
 *          },
 *     },
 *     "post"={
 *          "method"="POST",
 *          "swagger_context"={
 *               "parameters"={
 *                   {
 *                       "name"="Authorization",
 *                       "description"="Access token",
 *                       "in"="header",
 *                       "default"="Bearer {{token}}",
 *                       "required"=true,
 *                       "type"="string",
 *                   },
 *                   {
 *                       "name"="project",
 *                       "in"="body",
 *                       "description"="The new Project resource",
 *                       "schema"={"$ref"="#/definitions/Project"},
 *                   },
 *               },
 *          },
 *     },
 * },
 * itemOperations={
 *     "get"={
 *          "method"="GET",
 *          "swagger_context"={
 *               "parameters"={
 *                   {
 *                       "name"="Authorization",
 *                       "description"="Access token",
 *                       "in"="header",
 *                       "default"="Bearer {{token}}",
 *                       "required"=true,
 *                       "type"="string",
 *                   },
 *                   {
 *                      "name"="id",
 *                      "in"="path",
 *                      "description"="Resource UUID",
 *                      "required"=true,
 *                      "type"="string",
 *                      "format"="uuid",
 *                   },
 *               },
 *          },
 *     },
 *     "put"={
 *          "method"="PUT",
 *          "swagger_context"={
 *               "parameters"={
 *                   {
 *                       "name"="Authorization",
 *                       "description"="Access token",
 *                       "in"="header",
 *                       "default"="Bearer {{token}}",
 *                       "required"=true,
 *                       "type"="string",
 *                   },
 *                   {
 *                      "name"="id",
 *                      "in"="path",
 *                      "description"="Resource UUID",
 *                      "required"=true,
 *                      "type"="string",
 *                      "format"="uuid",
 *                   },
 *                   {
 *                       "name"="project",
 *                       "in"="body",
 *                       "description"="The new Project resource",
 *                       "schema"={"$ref"="#/definitions/Project"},
 *                   },
 *               },
 *          },
 *     },
 *     "delete"={
 *          "method"="DELETE",
 *          "swagger_context"={
 *               "parameters"={
 *                   {
 *                       "name"="Authorization",
 *                       "description"="Access token",
 *                       "in"="header",
 *                       "default"="Bearer {{token}}",
 *                       "required"=true,
 *                       "type"="string",
 *                   },
 *                   {
 *                      "name"="id",
 *                      "in"="path",
 *                      "description"="Resource UUID",
 *                      "required"=true,
 *                      "type"="string",
 *                      "format"="uuid",
 *                   },
 *               },
 *          },
 *     },
 * })
 *
 * @ORM\Entity()
 * @ORM\Table(name="projects")
 */
class Project
{
    /**
     * @var Uuid
     *
     * @ORM\Id
     * @ORM\Column(type="uuid")
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank()
     * @Assert\Length(min="3",max="100")
     */
    protected $name;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id")
     */
    protected $author;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true)
     *
     * @Gedmo\Slug(handlers={
     *      @Gedmo\SlugHandler(class="Gedmo\Sluggable\Handler\RelativeSlugHandler",options={
     *          @Gedmo\SlugHandlerOption(name="relationField",value="author"),
     *          @Gedmo\SlugHandlerOption(name="relationSlugField",value="username"),
     *          @Gedmo\SlugHandlerOption(name="separator",value="/"),
     *      }),
     * },fields={"name"},separator="-",updatable=true,unique=true)
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank()
     * @Assert\Length(min="10",max="300")
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     *
     * @Assert\Length(max="200")
     */
    protected $url;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
     */
    protected $createdAt;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="update")
     */
    protected $updatedAt;


    /**
     * @return Uuid|null
     */
    public function getId(): ?Uuid
    {
        return $this->id;
    }


    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }


    /**
     * @return string|null
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


    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }


    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }


    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }


    /**
     * @return User|null
     */
    public function getAuthor(): ?User
    {
        return $this->author;
    }


    /**
     * @param User $author
     */
    public function setAuthor(User $author): void
    {
        $this->author = $author;
    }


    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }


    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }


    /**
     * @return DateTime
     */
    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param UserInterface|null $user
     *
     * @return bool
     */
    public function isAuthor(UserInterface $user = null): bool
    {
        return $user instanceof self && $user->id === $this->id;
    }
}