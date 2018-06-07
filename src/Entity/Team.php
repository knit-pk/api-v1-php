<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(attributes={
 *     "normalization_context": {"groups": {"TeamRead"}},
 *     "denormalization_context": {"groups": {"TeamWrite"}},
 * },
 * collectionOperations={
 *     "get": {
 *         "method": "GET",
 *     },
 *     "post": {
 *         "method": "POST",
 *         "access_control": "is_granted('ROLE_USER_WRITER')",
 *     },
 * },
 * itemOperations={
 *     "get": {
 *         "method": "GET",
 *     },
 *     "put": {
 *         "method": "PUT",
 *         "access_control": "is_granted('ROLE_ADMIN') or (user and object.isAuthor(user))",
 *     },
 *     "delete": {
 *         "method": "DELETE",
 *         "access_control": "is_granted('ROLE_ADMIN') or (user and object.isAuthor(user))",
 *     },
 * })
 *
 * @ORM\Entity
 * @ORM\Table(name="teams")
 */
class Team
{
    /**
     * @var Uuid
     *
     * @ORM\Id
     * @ORM\Column(type="uuid")
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     *
     * @Groups({"TeamRead"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank
     * @Assert\Length(min="3", max="100")
     *
     * @Groups({"TeamRead", "TeamWrite"})
     */
    protected $name;

    /**
     * @var Collection|Team[]
     *
     * One Team has Many Teams
     * @ORM\OneToMany(targetEntity="Team", mappedBy="parent")
     *
     * @Groups({"TeamRead"})
     */
    protected $children;

    /**
     * @var Team
     *
     * Many Teams have One parent Team
     * @ORM\ManyToOne(targetEntity="Team", inversedBy="children")
     * @ORM\JoinColumn(name="parent_team_id", referencedColumnName="id")
     *
     * @Groups({"TeamWrite"})
     */
    protected $parent;

    /**
     * @var Collection|User[]
     *
     * @ORM\ManyToMany(targetEntity="User")
     * @ORM\JoinTable(name="teams_users", joinColumns={
     *     @ORM\JoinColumn(name="team_id", referencedColumnName="id", onDelete="CASCADE")
     * }, inverseJoinColumns={
     *     @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE"),
     * })
     *
     * @Groups({"TeamRead", "TeamWrite"})
     */
    protected $users;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function addUser(User $user): void
    {
        $this->users[] = $user;
    }

    public function addChild(self $team): void
    {
        $this->children[] = $team;
    }

    public function removeUser(User $user): void
    {
        $this->users->removeElement($user);
    }

    public function removeChild(self $team): void
    {
        $this->children->removeElement($team);
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(self $team): void
    {
        $this->parent = $team;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
