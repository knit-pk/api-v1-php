<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Role\Role;

/**
 * @ORM\Entity()
 * @ORM\Table(name="security_roles")
 */
class SecurityRole extends Role
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
     * @ORM\Column(name="role",type="string",unique=true,length=70)
     */
    protected $role;

    /**
     * @var User[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="User",mappedBy="securityRoles")
     * @ORM\JoinTable(name="users_security_roles")
     */
    protected $users;


    /**
     * SecurityRole constructor.
     *
     * @param string $role
     */
    public function __construct(?string $role = null)
    {
        parent::__construct($role);
        $this->role = $role;
        $this->users = new ArrayCollection();
    }


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
    public function getRole(): ?string
    {
        return $this->role;
    }


    public function setRole(string $role)
    {
        $this->role= $role;
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
        return (string) ($this->getRole() ?? '');
    }
}