<?php
declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DomainException;
use FOS\UserBundle\Model\User as FOSUser;
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
class User extends FOSUser
{
    public const ROLE_USER = 'ROLE_USER';

    public const ROLE_READER = 'ROLE_READER';

    public const ROLE_WRITER = 'ROLE_WRITER';

    public const ROLE_USER_WRITER = 'ROLE_USER_WRITER';

    public const ROLE_ADMIN = 'ROLE_ADMIN';

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
     * @var string nickname, an unique alias of the name
     *
     * @ApiProperty(iri="http://schema.org/alternateName")
     *
     * @Groups({"UserRead","UserReadLess","UserWrite"})
     */
    protected $username;

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
     * @var SecurityRole[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="SecurityRole",inversedBy="users")
     * @ORM\JoinTable(name="users_security_roles",
     *      joinColumns={
     *          @ORM\JoinColumn(name="user_id",referencedColumnName="id",onDelete="CASCADE")
     *      },inverseJoinColumns={
     *          @ORM\JoinColumn(name="security_role_id",referencedColumnName="id",onDelete="CASCADE")
     *      }
     * )
     *
     * @Groups({"UserAdminWrite", "UserAdminRead"})
     */
    protected $securityRoles;

    /**
     * @var bool
     *
     * @Groups({"UserAdminWrite", "UserAdminRead"})
     */
    protected $enabled;

    /**
     * @var \DateTime
     *
     * @Groups({"UserAdminRead"})
     */
    protected $lastLogin;


    /**
     * User constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->salt = '';
        $this->securityRoles = new ArrayCollection();
        $this->groups = new ArrayCollection();
    }


    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize([
            $this->password,
            $this->salt,
            $this->usernameCanonical,
            $this->username,
            $this->enabled,
            $this->id,
            $this->email,
            $this->emailCanonical,
        ]);
    }


    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        [
            $this->password,
            $this->salt,
            $this->usernameCanonical,
            $this->username,
            $this->enabled,
            $this->id,
            $this->email,
            $this->emailCanonical,
        ] = unserialize($serialized, ['allowed_classes' => true]);
    }

    /**
     * @param string $role
     *
     * @return \App\Entity\SecurityRole|null
     */
    public function getRole(string $role): ?SecurityRole
    {
        foreach ($this->securityRoles as $securityRole) {
            if ($role === $securityRole->getRole()) {
                return $securityRole;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \DomainException
     */
    public function addRole($role): User
    {
        if (!$role instanceof SecurityRole) {
            throw new DomainException('Can add only SecurityRole object as User role.');
        }

        $this->addSecurityRole($role);

        return $this;
    }


    /**
     * @inheritdoc
     */
    public function getRoles(): array
    {
        $roles = [];
        foreach ($this->securityRoles as $securityRole) {
            $roles[] = $securityRole->getRole();
        }

        return $roles;
    }


    /**
     * @inheritdoc
     */
    public function hasRole($role): bool
    {
        return $this->getRole($role) instanceof SecurityRole;
    }

    /**
     * @inheritdoc
     */
    public function removeRole($role): User
    {
        $securityRole = $this->getRole($role);
        if ($securityRole instanceof SecurityRole) {
            $this->removeSecurityRole($securityRole);
        }

        return $this;
    }


    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSecurityRoles(): Collection
    {
        return $this->securityRoles;
    }


    /**
     * @param \App\Entity\SecurityRole $role
     */
    public function addSecurityRole(SecurityRole $role): void
    {
        if (!$this->securityRoles->contains($role)) {
            $this->securityRoles[] = $role;
        }
    }


    /**
     * @param \App\Entity\SecurityRole $role
     */
    public function removeSecurityRole(SecurityRole $role): void
    {
        $this->securityRoles->removeElement($role);
    }


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