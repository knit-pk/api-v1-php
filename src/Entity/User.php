<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Security\User\UserInterface;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DomainException;
use FOS\UserBundle\Model\UserInterface as FOSUserInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An User of KNIT api, concrete type of Person.
 *
 * @see http://schema.org/Person Documentation on Schema.org
 *
 *
 * @ApiResource(iri="http://schema.org/Person",
 *     attributes={
 *         "filters": {"app.user.group_filter"},
 *         "normalization_context": {"groups": {"UserRead"}},
 *         "denormalization_context": {"groups": {"UserWrite"}},
 *     },
 *     collectionOperations={
 *         "get": {
 *             "method": "GET",
 *             "access_control": "is_granted('ROLE_READER')",
 *         },
 *         "post": {
 *             "method": "POST",
 *             "access_control": "is_granted('ROLE_USER_WRITER')",
 *         },
 *     },
 *     itemOperations={
 *         "get": {
 *             "method": "GET",
 *             "access_control": "is_granted('ROLE_READER')",
 *         },
 *         "put": {
 *             "method": "PUT",
 *             "access_control": "is_granted('ROLE_ADMIN') or (user and object.isUser(user))",
 *         },
 *         "delete": {
 *             "method": "DELETE",
 *             "access_control": "is_granted('ROLE_ADMIN') or (user and object.isUser(user))",
 *         },
 *     })
 *
 *     @ORM\Entity
 *     @ORM\Table(name="users")
 */
class User implements UserInterface, FOSUserInterface
{
    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid")
     *
     * @Groups({"UserRead", "UserReadLess"})
     */
    protected $id;

    /**
     * @var string nickname, an unique alias of the name
     *
     * @ApiProperty(iri="http://schema.org/alternateName")
     *
     * @ORM\Column(name="username", type="string", length=180)
     *
     * @Groups({"UserRead", "UserReadLess", "UserWrite"})
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column(name="username_canonical", type="string", length=180, unique=true)
     */
    protected $usernameCanonical;

    /**
     * @var string email address
     *
     * @ApiProperty(iri="http://schema.org/email")
     *
     * @ORM\Column(name="email", type="string", length=180)
     *
     * @Assert\Email
     *
     * @Groups({"UserRead", "UserReadLess", "UserWrite"})
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(name="email_canonical", type="string", length=180, unique=true)
     */
    protected $emailCanonical;

    /**
     * @var string real full name
     *
     * @ApiProperty(iri="http://schema.org/name")
     *
     * @ORM\Column(type="string", nullable=true)
     *
     * @Groups({"UserRead", "UserWrite", "UserReadLess"})
     */
    protected $fullname;

    /**
     * Encrypted password. Must be persisted.
     *
     * @ORM\Column(name="hash", type="string")
     *
     * @var string
     */
    protected $hash;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     *
     * @var string|null
     *
     * @Groups({"UserWrite"})
     */
    protected $plainPassword;

    /**
     * @ApiProperty(iri="http://schema.org/image")
     *
     * @var Image
     *
     * @ORM\ManyToOne(targetEntity="Image")
     * @ORM\JoinColumn(name="avatar_image_id", referencedColumnName="id", onDelete="RESTRICT")
     *
     * @Groups({"UserRead", "UserWrite", "UserReadLess"})
     */
    protected $avatar;

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
     * @ORM\ManyToMany(targetEntity="SecurityRole", inversedBy="users")
     * @ORM\JoinTable(name="users_security_roles",
     *     joinColumns={
     *         @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     *     }, inverseJoinColumns={
     *         @ORM\JoinColumn(name="security_role_id", referencedColumnName="id", onDelete="CASCADE")
     *     }
     * )
     *
     * @Groups({"UserAdminWrite", "UserAdminRead"})
     */
    protected $securityRoles;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean")
     *
     * @Groups({"UserAdminWrite", "UserAdminRead"})
     */
    protected $enabled;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="last_login", type="datetime", nullable=true)
     *
     * @Groups({"UserAdminRead"})
     */
    protected $lastLogin;

    /**
     * Random string sent to the user email address in order to verify it.
     *
     * @ORM\Column(name="confirmation_token", type="string", length=180, unique=true, nullable=true)
     *
     * @var string
     */
    protected $confirmationToken;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="password_requested_at", type="datetime", nullable=true)
     */
    protected $passwordRequestedAt;

    /**
     * @var bool
     *
     * @ORM\Column(name="super_admin", type="boolean")
     *
     * @Groups({"UserAdminRead"})
     */
    private $superAdmin;

    public function __construct(UuidInterface $id)
    {
        $this->id = $id;
        $this->enabled = false;
        $this->superAdmin = false;
        $this->securityRoles = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): string
    {
        return \serialize([
            $this->id,
            $this->username,
            $this->usernameCanonical,
            $this->email,
            $this->emailCanonical,
            $this->hash,
            $this->enabled,
            $this->superAdmin,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized): void
    {
        [
            $this->id,
            $this->username,
            $this->usernameCanonical,
            $this->email,
            $this->emailCanonical,
            $this->hash,
            $this->enabled,
            $this->superAdmin,
        ] = \unserialize($serialized, ['allowed_classes' => true]);
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @return Image
     */
    public function getAvatar(): ?Image
    {
        return $this->avatar;
    }

    /**
     * @param Image $avatar
     */
    public function setAvatar(?Image $avatar): void
    {
        $this->avatar = $avatar;
    }

    /**
     * @param string $role
     *
     * @return SecurityRole|null
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
    public function setRoles(array $roles)
    {
        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    /**
     * @param SecurityRole $role
     *
     * @throws \DomainException
     *
     * @return self
     */
    public function addRole($role): self
    {
        if (!$role instanceof SecurityRole) {
            throw new DomainException('Can add only SecurityRole object as User role.');
        }

        $this->addSecurityRole($role);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles(): array
    {
        $roles = [];

        foreach ($this->securityRoles as $securityRole) {
            $roles[] = $securityRole->getRole();
        }

        $roles[] = static::ROLE_DEFAULT;
        if ($this->superAdmin) {
            $roles[] = static::ROLE_SUPER_ADMIN;
        }

        return \array_values(\array_unique($roles));
    }

    /**
     * {@inheritdoc}
     */
    public function hasRole($role): bool
    {
        return $this->getRole($role) instanceof SecurityRole;
    }

    /**
     * {@inheritdoc}
     */
    public function removeRole($role): self
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
     * @param SecurityRole $role
     */
    public function addSecurityRole(SecurityRole $role): void
    {
        if (!$this->securityRoles->contains($role)) {
            $this->securityRoles[] = $role;
        }
    }

    /**
     * @param SecurityRole $role
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
     * {@inheritdoc}
     */
    public function isUser(?UserInterface $user): bool
    {
        return $user instanceof UserInterface && $this->id->equals($user->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonExpired(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonLocked(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setUsernameCanonical($usernameCanonical)
    {
        $this->usernameCanonical = $usernameCanonical;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSalt($salt)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailCanonical(): string
    {
        return $this->emailCanonical;
    }

    /**
     * {@inheritdoc}
     */
    public function setEmailCanonical($emailCanonical)
    {
        $this->emailCanonical = $emailCanonical;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * {@inheritdoc}
     */
    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;
    }

    /**
     * {@inheritdoc}
     */
    public function setPassword($hash)
    {
        $this->hash = $hash;
    }

    /**
     * {@inheritdoc}
     */
    public function isSuperAdmin(): bool
    {
        return $this->superAdmin || $this->hasRole(static::ROLE_SUPER_ADMIN);
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled($boolean)
    {
        $this->enabled = $boolean;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSuperAdmin($boolean)
    {
        if (!$this->superAdmin = $boolean) {
            $this->removeRole(static::ROLE_SUPER_ADMIN);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfirmationToken($confirmationToken)
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPasswordRequestedAt(?DateTime $date = null)
    {
        $this->passwordRequestedAt = $date;

        return $this;
    }

    /**
     * Gets the timestamp that the user requested a password reset.
     *
     * @return DateTime|null
     */
    public function getPasswordRequestedAt(): ?DateTime
    {
        return $this->passwordRequestedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function isPasswordRequestNonExpired($ttl): int
    {
        $passwordRequestedAt = $this->getPasswordRequestedAt();

        return (int) ($passwordRequestedAt instanceof DateTime &&
            $passwordRequestedAt->getTimestamp() + $ttl > \time());
    }

    /**
     * {@inheritdoc}
     */
    public function setLastLogin(?DateTime $time = null)
    {
        $this->lastLogin = $time;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword(): string
    {
        return $this->hash;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsernameCanonical(): string
    {
        return $this->usernameCanonical;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    /**
     * @return DateTime|null
     */
    public function getLastLogin(): ?DateTime
    {
        return $this->lastLogin;
    }

    public function __toString()
    {
        return (string) $this->getUsername();
    }
}
