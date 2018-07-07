<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(attributes={
 *     "normalization_context": {"groups": {"SecurityRoleRead"}},
 *     "denormalization_context": {"groups": {"SecurityRoleWrite"}},
 * },
 * collectionOperations={
 *     "get": {
 *         "method": "GET",
 *     },
 * },
 * itemOperations={
 *     "get": {
 *         "method": "GET",
 *     },
 * })
 *
 * @ORM\Entity
 * @ORM\Table(name="security_roles")
 */
class SecurityRole extends Role
{
    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid")
     *
     * @Groups({"SecurityRoleRead"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ApiProperty(iri="http://schema.org/name")
     *
     * @ORM\Column(name="name", type="string", length=70)
     *
     * @Groups({"SecurityRoleAdminRead"})
     */
    protected $name;

    /**
     * @var string
     *
     * @ApiProperty(iri="http://schema.org/alternateName")
     *
     * @ORM\Column(name="role", type="string", unique=true, length=70)
     *
     * @Groups({"SecurityRoleAdminRead"})
     */
    protected $role;

    /**
     * @var User[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="User", mappedBy="securityRoles", cascade={"persist"})
     */
    protected $users;

    public function __construct(UuidInterface $id, string $role)
    {
        parent::__construct($role);
        $this->id = $id;
        $this->role = $role;
        $this->users = new ArrayCollection();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * @param User $user
     */
    public function addUser(User $user): void
    {
        $user->addSecurityRole($this);
        $this->users[] = $user;
    }

    public function removeUser(User $user): void
    {
        $this->users->removeElement($user);
    }

    public function isSecurityRole(?Role $role): bool
    {
        return $role instanceof self && $role->getRole() === $this->getRole();
    }

    public function __toString()
    {
        return (string) $this->getName();
    }
}
