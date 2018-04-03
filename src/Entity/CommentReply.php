<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Security\User\UserInterface;
use App\Thought\ThoughtfulInterface;
use App\Thought\ThoughtInterface;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A comment on an item - for example, a comment on a blog post. The comment's content is expressed via the \[\[text\]\] property, and its topic via \[\[about\]\], properties shared with all CreativeWorks.
 *
 * @see http://schema.org/Comment Documentation on Schema.org
 *
 * @ApiResource(iri="http://schema.org/Answer",
 *     attributes={
 *         "filters": {"app.comment_reply.search_filter", "app.comment_reply.order_filter", "app.comment_reply.date_filter", "app.comment_reply.group_filter"},
 *         "normalization_context": {"groups": {"ReplyRead"}},
 *         "denormalization_context": {"groups": {"ReplyWrite"}},
 *     },
 *     collectionOperations={
 *         "get": {
 *             "method": "GET",
 *         },
 *         "post": {
 *             "method": "POST",
 *             "access_control": "is_granted('ROLE_READER')",
 *         },
 *         "comment_add_comment_reply": {
 *             "route_name": "comment_add_comment_reply",
 *             "access_control": "is_granted('ROLE_READER')",
 *             "denormalization_context": {"groups": {"ReplyWriteLess"}},
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
 *     @ORM\Table(name="comment_replies")
 */
class CommentReply implements ThoughtInterface
{
    /**
     * @var Uuid
     *
     * @ORM\Id
     * @ORM\Column(type="uuid")
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     *
     * @Groups({"ReplyRead"})
     */
    protected $id;

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
     * @Groups({"ReplyRead", "ReplyWrite", "ReplyReadLess"})
     */
    protected $author;

    /**
     * @var Comment
     *
     * Many Replies have One Comment
     * @ORM\ManyToOne(targetEntity="Comment", inversedBy="replies")
     * @ORM\JoinColumn(name="comment_id", referencedColumnName="id")
     *
     * @ApiProperty(iri="http://schema.org/parentItem")
     *
     * @Assert\NotBlank
     *
     * @Groups({"ReplyWrite", "CommentReplyAdminRead"})
     */
    protected $comment;

    /**
     * @var string|null the textual content of this CreativeWork
     *
     * @ORM\Column(type="text")
     *
     * @ApiProperty(iri="http://schema.org/name")
     *
     * @Assert\NotBlank
     *
     * @Groups({"ReplyRead", "ReplyWrite", "ReplyReadLess", "ReplyWriteLess"})
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
     * @Groups({"ReplyRead", "ReplyReadLess"})
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
     * @Groups({"ReplyRead", "ReplyReadLess"})
     */
    protected $createdAt;

    public function getId(): Uuid
    {
        return $this->id;
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

    public function getAuthor(): UserInterface
    {
        return $this->author;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getComment(): Comment
    {
        return $this->comment;
    }

    public function setComment(Comment $comment): void
    {
        $this->comment = $comment;
    }

    public function isAuthor(?UserInterface $user): bool
    {
        $author = $this->getAuthor();

        return $author instanceof UserInterface && $author->isUser($user);
    }

    public function __toString()
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
        if (!$subject instanceof Comment) {
            throw new RuntimeException('Subject must be an Comment instance');
        }

        $this->comment = $subject;
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        return $this->getText();
    }
}
