<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Security\User\UserInterface;
use App\Thought\ThoughtfulInterface;
use App\Thought\ThoughtInterface;
use Assert\Assertion;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use RuntimeException;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(iri="http://schema.org/Rating",
 *     attributes={
 *         "normalization_context": {"groups": {"RatingRead"}},
 *         "denormalization_context": {"groups": {"RatingWrite"}},
 *     },
 *     collectionOperations={
 *         "get": {
 *             "method": "GET",
 *         },
 *         "post": {
 *             "method": "POST",
 *             "access_control": "is_granted('ROLE_READER')",
 *         },
 *         "article_add_rating": {
 *             "route_name": "article_add_rating",
 *             "access_control": "is_granted('ROLE_READER')",
 *             "denormalization_context": {"groups": {"RatingWriteLess"}},
 *         },
 *     },
 *     itemOperations={
 *         "get": {
 *             "method": "GET",
 *         },
 *         "delete": {
 *             "method": "DELETE",
 *             "access_control": "is_granted('ROLE_ADMIN') or (user and object.isAuthor(user))",
 *         },
 *     })
 *
 *     @ORM\Entity
 *     @ORM\Table(name="ratings", uniqueConstraints={
 *         @ORM\UniqueConstraint(name="user_article_unique", columns={"author_id", "article_id"})
 *     })
 *     @UniqueEntity(fields={"article", "author"}, message="An user has already rated this article.")
 */
class Rating implements ThoughtInterface
{
    public const RATING_LIKE = 'LIKE';

    public const SUPPORTED_RATINGS = [
        self::RATING_LIKE,
    ];

    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid")
     *
     * @Groups({"RatingRead"})
     */
    protected $id;

    /**
     * @var Article
     *
     * @ORM\ManyToOne(targetEntity="Article", inversedBy="ratings")
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @Assert\NotBlank
     *
     * @Groups({"RatingRead", "RatingWrite"})
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
     * @Groups({"RatingRead", "RatingWrite"})
     */
    protected $author;

    /**
     * @var string|null The rating for the content
     *
     * @ORM\Column(type="string")
     *
     * @ApiProperty(iri="http://schema.org/ratingValue", attributes={
     *     "swagger_context": {
     *         "type": "string",
     *         "enum": {"LIKE"},
     *         "example": "LIKE",
     *     },
     * })
     *
     * @Groups({"RatingRead", "RatingWrite", "RatingWriteLess"})
     */
    protected $value;

    /**
     * @var DateTime|null the date on which the CreativeWork was created or the item was added to a DataFeed
     *
     * @ORM\Column(type="datetime")
     *
     * @ApiProperty(iri="http://schema.org/dateCreated")
     *
     * @Gedmo\Timestampable(on="create")
     *
     * @Groups({"RatingRead"})
     */
    protected $createdAt;

    public function __construct(UuidInterface $id, string $value = self::RATING_LIKE)
    {
        Assertion::inArray($value, static::SUPPORTED_RATINGS, 'Review value "%s" is not among valid values: %s');

        $this->id = $id;
        $this->value = $value;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function setArticle(Article $article): void
    {
        $this->article = $article;
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

    public function setCreatedAt(?DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getAuthor(): ?UserInterface
    {
        return $this->author;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
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
     * Expresses thought provided by its author in readable form.
     *
     * @return string
     */
    public function toString(): string
    {
        return (string) $this->getValue();
    }
}
