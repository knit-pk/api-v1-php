<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(attributes={
 *     "normalization_context"={"groups"={"SecurityRoleRead"}},
 *     "denormalization_context"={"groups"={"SecurityRoleWrite"}},
 * },
 * collectionOperations={
 *     "get"={
 *          "method"="GET",
 *     },
 * },
 * itemOperations={
 *     "get"={
 *          "method"="GET",
 *     },
 * })
 *
 * @ORM\Entity()
 * @ORM\Table(name="security_roles")
 */
class SecurityRole extends Role
{
    /**
     * @var Uuid
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid")
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     *
     * @Groups({"SecurityRoleRead"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ApiProperty(iri="http://schema.org/name")
     *
     * @ORM\Column(name="name",type="string",length=70)
     *
     * @Groups({"SecurityRoleAdminRead"})
     */
    protected $name;

    /**
     * @var string
     *
     * @ApiProperty(iri="http://schema.org/alternateName")
     *
     * @ORM\Column(name="role",type="string",unique=true,length=70)
     *
     * @Groups({"SecurityRoleAdminRead"})
     */
    protected $role;

    /**
     * @var User[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="User",mappedBy="securityRoles",cascade={"persist"})
     */
    protected $users;

    /**
     * SecurityRole constructor.
     *
     * @param string $role
     */
    public function __construct(?string $role = null)
    {
        $this->users = new ArrayCollection();
        $this->setRole($role);
    }

    /**
     * @return Uuid|null
     */
    public function getId(): ?Uuid
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * @param string|null $role
     */
    public function setRole(?string $role)
    {
        $this->role = $role;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return User[]|Collection
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * @param User $user
     */
    public function addUser(User $user)
    {
        $user->addSecurityRole($this);
        $this->users[] = $user;
    }

    public function removeUser(User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * @param Role|null $role
     *
     * @return bool
     */
    public function isSecurityRole(?Role $role): bool
    {
        return $role instanceof self && $role->getRole() === $this->getRole();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }
}
