<?php
declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as Base;
use Ramsey\Uuid\Uuid;
use FOS\UserBundle\Model\UserInterface;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(attributes={
 *     "normalization_context"={"groups"={"user", "user-read"}},
 *     "denormalization_context"={"groups"={"user", "user-write"}},
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
 *          "normalization_context"={"groups"={"user","user-write"}},
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
 *                       "name"="user",
 *                       "in"="body",
 *                       "description"="The new User resource",
 *                       "schema"={"$ref"="#/definitions/User-user_user-write"},
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
 *                       "name"="user",
 *                       "in"="body",
 *                       "description"="The new User resource",
 *                       "schema"={"$ref"="#/definitions/User-user_user-write"},
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
 *     "login"={
 *          "route_name"="api_login_check",
 *          "method"="POST",
 *          "swagger_context"={
 *              "summary"="Authenticate using credentials",
 *              "description"="Authenticate using credentials",
 *              "parameters"={
 *                  {
 *                      "name"="credentials",
 *                      "in"="body",
 *                      "description"="User authentication credentials",
 *                      "schema"={
 *                          "type"="object",
 *                          "properties"={
 *                              "username"={
 *                                  "type"="string",
 *                              },
 *                              "password"={
 *                                  "type"="string",
 *                              },
 *                          },
 *                      },
 *                  },
 *              },
 *              "responses"={
 *                  "200"={
 *                      "description"="Successfully authenticated",
 *                      "schema"={
 *                          "type"="object",
 *                          "properties"={
 *                              "token"={
 *                                  "type"="string",
 *                                  "description"="JWT",
 *                              },
 *                          },
 *                      },
 *                  },
 *                  "401"={
 *                      "description"="Invalid credentials",
 *                      "schema"={
 *                          "type"="object",
 *                          "properties"={
 *                              "code"={
 *                                  "type"="string",
 *                                  "description"="Error code",
 *                              },
 *                              "message"={
 *                                  "type"="string",
 *                                  "description"="Error message",
 *                              },
 *                          },
 *                      },
 *                  },
 *              },
 *          },
 *     },
 * })
 *
 * @ORM\Entity
 * @ORM\Table(name="users")
 *
 * @method Uuid getId
 */
class User extends Base
{
    /**
     * @var Uuid
     *
     * @ORM\Id
     * @ORM\Column(type="uuid_binary_ordered_time")
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidOrderedTimeGenerator")
     *
     * @Groups({"user-read"})
     */
    protected $id;

    /**
     * @var string
     *
     * @Groups({"user"})
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(type="string",nullable=true)
     *
     * @Groups({"user"})
     */
    protected $fullname;

    /**
     * @var string
     *
     * @Groups({"user-write"})
     */
    protected $plainPassword;

    /**
     * @var string
     *
     * @Groups({"user"})
     */
    protected $username;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
     *
     * @Groups({"user-read"})
     */
    protected $createdAt;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="update")
     *
     * @Groups({"user-read"})
     */
    protected $updatedAt;


    /**
     * @param string|null $fullname
     *
     * @return $this
     */
    public function setFullname(?string $fullname)
    {
        $this->fullname = $fullname;

        return $this;
    }


    /**
     * @return string|null
     */
    public function getFullname(): ?string
    {
        return $this->fullname;
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
    public function isUser(UserInterface $user = null): bool
    {
        return $user instanceof self && $user->id === $this->id;
    }
}