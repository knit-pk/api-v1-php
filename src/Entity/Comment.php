<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Security\User\UserInterface;
use App\Thought\ThoughtfulInterface;
use App\Thought\ThoughtInterface;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use RuntimeException;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A comment on an item - for example, a comment on a blog post. The comment's content is expressed via the \[\[text\]\] property, and its topic via \[\[about\]\], properties shared with all CreativeWorks.
 *
 * @see http://schema.org/Comment Documentation on Schema.org
 *
 * @ApiResource(iri="http://schema.org/Comment",
 *     attributes={
 *         "filters": {"app.comment.search_filter", "app.comment.group_filter", "app.comment.order_filter", "app.comment.date_filter"},
 *         "normalization_context": {"groups": {"CommentRead"}},
 *         "denormalization_context": {"groups": {"CommentWrite"}},
 *     },
 *     collectionOperations={
 *         "get": {
 *             "method": "GET",
 *         },
 *         "post": {
 *             "method": "POST",
 *             "access_control": "is_granted('ROLE_READER')",
 *         },
 *         "article_add_comment": {
 *             "route_name": "article_add_comment",
 *             "access_control": "is_granted('ROLE_READER')",
 *             "denormalization_context": {"groups": {"CommentWriteLess"}},
 *         },
 *     },
 *     itemOperations={
 *         "get": {
 *             "method": "GET",
 *         },
 *         "put": {
 *             "method": "PUT",
 *             "access_control": "is_granted('ROLE_ADMIN') or (user and object.isAuthor(user))",
 *         },
 *         "delete": {
 *             "method": "DELETE",
 *             "access_control": "is_granted('ROLE_ADMIN') or (user and object.isAuthor(user))",
 *         },
 *     })
 *
 *     @ORM\Entity
 *     @ORM\Table(name="comments")
 */
class Comment implements ThoughtInterface, ThoughtfulInterface
{
    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid")
     *
     * @Groups({"CommentRead"})
     */
    protected $id;

    /**
     * @var Article
     *
     * @ORM\ManyToOne(targetEntity="Article", inversedBy="comments")
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @ApiProperty(iri="http://schema.org/parentItem")
     *
     * @Assert\NotBlank
     *
     * @Groups({"CommentRead", "CommentWrite"})
     */
    protected $article;

    /**
     * @var User The author of this content or rating. Please note that author is special in that HTML 5 provides a special mechanism for indicating authorship via the rel tag. That is equivalent to this and may be used interchangeably.
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @ApiProperty(iri="http://schema.org/author")
     *
     * @Assert\NotBlank
     *
     * @Groups({"CommentRead", "CommentWrite"})
     */
    protected $author;

    /**
     * @var Collection|CommentReply[]
     *
     * One Comment has Many Replies
     * @ORM\OneToMany(targetEntity="CommentReply", mappedBy="comment", cascade={"remove"})
     *
     * @Groups({"CommentRead"})
     */
    protected $replies;

    /**
     * @var string|null the textual content of this CreativeWork
     *
     * @ORM\Column(type="text")
     *
     * @ApiProperty(iri="http://schema.org/name")
     *
     * @Assert\NotBlank
     *
     * @Groups({"CommentRead", "CommentWrite", "CommentWriteLess"})
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

    public function __construct(UuidInterface $id)
    {
        $this->id = $id;
        $this->replies = new ArrayCollection();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function setArticle(Article $article): void
    {
        if ($this->article instanceof Article) {
            $this->article->removeComment($this);
        }
        $this->article = $article;
        $article->addComment($this);
    }

    public function getArticle(): Article
    {
        return $this->article;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function setAuthor(UserInterface $author): void
    {
        if (!$author instanceof User) {
            throw new RuntimeException('Author must be an User entity');
        }

        $this->author = $author;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function getAuthor(): ?UserInterface
    {
        return $this->author;
    }

    public function getText(): string
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

    public function addReply(CommentReply $reply): void
    {
        if (!$this->replies->contains($reply)) {
            $this->replies[] = $reply;
            $this->article->incrementCommentsCount();
        }
    }

    public function removeReply(CommentReply $reply): void
    {
        if ($this->replies->contains($reply)) {
            $this->replies->removeElement($reply);
            $this->article->decrementCommentsCount();
        }
    }

    public function getReplies(): Collection
    {
        return $this->replies;
    }

    public function isAuthor(?UserInterface $user): bool
    {
        $author = $this->getAuthor();

        return $author instanceof UserInterface && $author->isUser($user);
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function setSubject(ThoughtfulInterface $subject): void
    {
        if (!$subject instanceof Article) {
            throw new RuntimeException('Subject must be an instance of Article');
        }

        $this->setArticle($subject);
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        return $this->getText();
    }

    /**
     * {@inheritdoc}
     */
    public function isThoughtSupported(ThoughtInterface $thought): bool
    {
        return $thought instanceof CommentReply;
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedThoughts(): array
    {
        return [CommentReply::class];
    }
}
