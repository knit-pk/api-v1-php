<?php
declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as Base;
use FOS\UserBundle\Model\UserInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An User of KNIT api, concrete type of Person.
 *
 * @see http://schema.org/Person Documentation on Schema.org
 *
 *
 * @ApiResource(iri="http://schema.org/Person",
 * attributes={
 *     "normalization_context"={"groups"={"UserRead"}},
 *     "denormalization_context"={"groups"={"UserWrite"}},
 * },
 * collectionOperations={
 *     "get"={
 *          "method"="GET",
 *          "access_control"="is_granted('ROLE_READER')",
 *     },
 *     "post"={
 *          "method"="POST",
 *          "access_control"="is_granted('ROLE_USER_WRITER')",
 *     },
 * },
 * itemOperations={
 *     "get"={
 *          "method"="GET",
 *          "access_control"="is_granted('ROLE_READER')",
 *     },
 *     "put"={
 *          "method"="PUT",
 *          "access_control"="is_granted('ROLE_ADMIN') or (user and object.isUser(user))",
 *     },
 *     "delete"={
 *          "method"="DELETE",
 *          "access_control"="is_granted('ROLE_ADMIN') or (user and object.isUser(user))",
 *     },
 * })
 *
 * @ORM\Entity()
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
     * @ORM\Column(type="uuid")
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     *
     * @Groups({"UserRead","UserReadLess"})
     */
    protected $id;

    /**
     * @var string email address
     *
     * @ApiProperty(iri="http://schema.org/email")
     *
     * @Assert\Email()
     *
     * @Groups({"UserRead","UserReadLess","UserWrite"})
     */
    protected $email;

    /**
     * @var string real full name
     *
     * @ApiProperty(iri="http://schema.org/name")
     *
     * @ORM\Column(type="string",nullable=true)
     *
     * @Groups({"UserRead","UserWrite"})
     */
    protected $fullname;

    /**
     * @var string
     *
     * @Groups({"UserWrite"})
     */
    protected $plainPassword;

    /**
     * @var string nickname, an unique alias of the name
     *
     * @ApiProperty(iri="http://schema.org/alternateName")
     *
     * @Groups({"UserRead","UserReadLess","UserWrite"})
     */
    protected $username;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
     *
     * @Groups({"UserRead"})
     */
    protected $createdAt;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="update")
     *
     * @Groups({"UserRead"})
     */
    protected $updatedAt;

    /**
     * @Groups({"UserAdminWrite", "UserAdminRead"})
     */
    protected $roles;

    /**
     * @Groups({"UserAdminWrite", "UserAdminRead"})
     */
    protected $enabled;

    /**
     * @Groups({"UserAdminRead"})
     */
    protected $lastLogin;


    /**
     * @param string|null $fullname
     */
    public function setFullname(?string $fullname)
    {
        $this->fullname = $fullname;
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
    public function isUser(?UserInterface $user): bool
    {
        return $user instanceof self && $user->id === $this->id;
    }
}