<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\UserInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A comment on an item - for example, a comment on a blog post. The comment's content is expressed via the \[\[text\]\] property, and its topic via \[\[about\]\], properties shared with all CreativeWorks.
 *
 * @see http://schema.org/Comment Documentation on Schema.org
 *
 * @ApiResource(iri="http://schema.org/Comment",
 * attributes={
 *     "filters"={"app.comment.search_filter"},
 *     "normalization_context"={"groups"={"CommentRead"}},
 *     "denormalization_context"={"groups"={"CommentWrite"}},
 * },
 * collectionOperations={
 *     "get"={
 *          "method"="GET",
 *     },
 *     "post"={
 *          "method"="POST",
 *          "access_control"="is_granted('ROLE_READER')",
 *     },
 * },
 * itemOperations={
 *     "get"={
 *          "method"="GET",
 *     },
 *     "put"={
 *          "method"="PUT",
 *          "access_control"="is_granted('ROLE_ADMIN') or (user and object.isAuthor(user))",
 *     },
 *     "delete"={
 *          "method"="DELETE",
 *          "access_control"="is_granted('ROLE_ADMIN') or (user and object.isAuthor(user))",
 *     },
 * })
 *
 * @ORM\Entity()
 * @ORM\Table(name="comments")
 */
class Comment
{
    /**
     * @var Uuid
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid")
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     *
     * @Groups({"CommentRead"})
     */
    protected $id;

    /**
     * @var Article
     *
     * @ORM\ManyToOne(targetEntity="Article",inversedBy="comments")
     * @ORM\JoinColumn(name="article_id",referencedColumnName="id",onDelete="CASCADE")
     *
     * @Assert\NotBlank()
     *
     * @Groups({"CommentRead","CommentWrite"})
     */
    protected $article;

    /**
     * @var User The author of this content or rating. Please note that author is special in that HTML 5 provides a special mechanism for indicating authorship via the rel tag. That is equivalent to this and may be used interchangeably.
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="author_id",referencedColumnName="id",onDelete="CASCADE")
     *
     * @ApiProperty(iri="http://schema.org/author")
     *
     * @Assert\NotBlank()
     *
     * @Groups({"CommentRead","CommentWrite"})
     */
    protected $author;

    /**
     * @var Collection|Comment[]
     *
     * One Team has Many Comments.
     * @ORM\OneToMany(targetEntity="Comment",mappedBy="parent")
     *
     * @Groups({"CommentRead"})
     */
    protected $replies;

    /**
     * @var Comment
     *
     * Many Comments have One parent Comment.
     * @ORM\ManyToOne(targetEntity="Comment",inversedBy="replies")
     * @ORM\JoinColumn(name="parent_comment_id",referencedColumnName="id")
     *
     * @Groups({"CommentWrite"})
     */
    protected $parent;

    /**
     * @var string|null the textual content of this CreativeWork
     *
     * @ORM\Column(type="text")
     *
     * @ApiProperty(iri="http://schema.org/text")
     *
     * @Assert\NotBlank()
     *
     * @Groups({"CommentRead","CommentWrite","CommentWriteLess"})
     */
    protected $text;

    /**
     * @var DateTime|null the date on which the CreativeWork was most recently modified or when the item's entry was modified within a DataFeed
     *
     * @ORM\Column(type="datetime")
     *
     * @ApiProperty(iri="http://schema.org/dateModified")
     *
     * @Gedmo\Timestampable(on="update")
     *
     * @Groups({"CommentRead"})
     */
    protected $updatedAt;

    /**
     * @var DateTime|null the date on which the CreativeWork was created or the item was added to a DataFeed
     *
     * @ORM\Column(type="datetime")
     *
     * @ApiProperty(iri="http://schema.org/dateCreated")
     *
     * @Gedmo\Timestampable(on="create")
     *
     * @Groups({"CommentRead"})
     */
    protected $createdAt;

    public function __construct()
    {
        $this->replies = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setArticle(?Article $article): void
    {
        $this->article = $article;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setAuthor(?User $author): void
    {
        $this->author = $author;
    }

    public function setText(?string $text): void
    {
        $this->text = $text;
    }

    public function setUpdatedAt(?DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function setCreatedAt(?DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function addReply(self $comment): void
    {
        $this->replies[] = $comment;
    }

    public function removeReply(self $comment): void
    {
        $this->replies->removeElement($comment);
    }

    public function getReplies(): Collection
    {
        return $this->replies;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(self $comment): void
    {
        $this->parent = $comment;
    }

    public function isAuthor(?UserInterface $user): bool
    {
        $author = $this->getAuthor();

        if (!$author instanceof User) {
            return false;
        }

        return $author->isUser($user);
    }

    public function __toString()
    {
        return (string) $this->getId();
    }
}
