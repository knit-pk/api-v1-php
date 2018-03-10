<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
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
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An article, such as a news article or piece of investigative report. Newspapers and magazines have articles of many different types and this is intended to cover them all.\\n\\nSee also \[blog post\](http://blog.schema.org/2014/09/schemaorg-support-for-bibliographic\_2.html).
 *
 * @see http://schema.org/Article Documentation on Schema.org
 *
 * @ApiResource(iri="http://schema.org/Article",
 *     attributes={
 *         "filters": {"app.article.search_filter", "app.article.boolean_filter", "app.article.group_filter", "app.article.order_filter"},
 *         "normalization_context": {"groups": {"ArticleRead"}},
 *         "denormalization_context": {"groups": {"ArticleWrite"}},
 *     },
 *     graphql={
 *         "query": {
 *             "normalization_context": {"groups": {"ArticleRead", "UserReadLess", "ImageReadLess", "TagRead", "CategoryRead"}},
 *         },
 *     },
 *     collectionOperations={
 *         "get": {
 *             "method": "GET",
 *         },
 *         "post": {
 *             "method": "POST",
 *             "access_control": "is_granted('ROLE_READER')",
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
 *     @ORM\Table(name="articles")
 */
class Article implements ThoughtfulInterface
{
    /**
     * @var \Ramsey\Uuid\UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid")
     *
     * @Groups({"ArticleRead"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true)
     *
     * @Groups({"ArticleRead"})
     */
    protected $code;

    /**
     * @var string|null the title of the article
     *
     * @ORM\Column(type="string", nullable=false)
     *
     * @ApiProperty(iri="http://schema.org/name")
     *
     * @Assert\NotBlank
     * @Assert\Length(min="3", max="100")
     *
     * @Groups({"ArticleRead", "ArticleWrite"})
     */
    protected $title;

    /**
     * @var string|null the actual body of the article
     *
     * @ORM\Column(type="text")
     *
     * @ApiProperty(iri="http://schema.org/articleBody")
     *
     * @Assert\NotBlank
     *
     * @Groups({"ArticleRead", "ArticleWrite"})
     */
    protected $content;

    /**
     * @var Category articles may belong to one category
     *
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     *
     * @ApiProperty(iri="http://schema.org/articleSection")
     *
     * @Assert\NotBlank
     *
     * @Groups({"ArticleRead", "ArticleWrite"})
     */
    protected $category;

    /**
     * @var ArrayCollection|Tag[] tags that defines article
     *
     * @ORM\ManyToMany(targetEntity="Tag")
     * @ORM\JoinTable(name="articles_tags",
     *     joinColumns={
     *         @ORM\JoinColumn(name="article_id", referencedColumnName="id", onDelete="CASCADE")
     *         }, inverseJoinColumns={
     *         @ORM\JoinColumn(name="tag_id", referencedColumnName="id", onDelete="CASCADE")
     *     },
     * )
     *
     * @Groups({"ArticleRead", "ArticleWrite"})
     */
    protected $tags;

    /**
     * @var ArrayCollection|Comment[] comments, typically from users
     *
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="article", cascade={"remove"})
     *
     * @Groups({"ArticleRead", "ArticleWrite"})
     *
     * @ApiSubresource
     */
    protected $comments;

    /**
     * @var ArrayCollection|Rating[] ratings, typically from users
     *
     * @ORM\OneToMany(targetEntity="Rating", mappedBy="article", cascade={"remove"})
     *
     * @Groups({"ArticleRead", "ArticleWrite"})
     *
     * @ApiSubresource
     */
    protected $ratings;

    /**
     * @var int Aggregate field that contains total number of comments and its replies
     *
     * @ORM\Column(type="integer", options={"unsigned": true})
     *
     * @ApiProperty(iri="http://schema.org/commentCount")
     *
     * @Groups({"ArticleRead"})
     */
    protected $commentsCount;

    /**
     * @var string|null the subject matter of the content
     *
     * @ORM\Column(type="text")
     *
     * @ApiProperty(iri="http://schema.org/about")
     *
     * @Assert\NotBlank
     * @Assert\Length(max="300")
     *
     * @Groups({"ArticleRead", "ArticleWrite"})
     */
    protected $description;

    /**
     * @var User The author of this content or rating. Please note that author is special in that HTML 5 provides a special mechanism for indicating authorship via the rel tag. That is equivalent to this and may be used interchangeably.
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     *
     * @ApiProperty(iri="http://schema.org/author")
     *
     * @Assert\NotBlank
     *
     * @Groups({"ArticleRead", "ArticleWrite"})
     */
    protected $author;

    /**
     * @var Image|null an image of the item
     *
     * @ORM\ManyToOne(targetEntity="Image")
     * @ORM\JoinColumn(name="image_id", referencedColumnName="id", onDelete="RESTRICT")
     *
     * @ApiProperty(iri="http://schema.org/image")
     *
     * @Groups({"ArticleRead", "ArticleWrite"})
     */
    protected $image;

    /**
     * @var DateTime|null date of first broadcast/publication
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @ApiProperty(iri="http://schema.org/datePublished")
     *
     * @Groups({"ArticleRead"})
     */
    protected $publishedAt;

    /**
     * @var bool determines whether article was published
     *
     * @ORM\Column(type="boolean")
     *
     * @Groups({"ArticleRead", "ArticleAdminUpdate"})
     */
    protected $published;

    /**
     * @var DateTime|null the date on which the CreativeWork was most recently modified or when the item's entry was modified within a DataFeed
     *
     * @ORM\Column(type="datetime")
     *
     * @ApiProperty(iri="http://schema.org/dateModified")
     *
     * @Gedmo\Timestampable(on="update")
     *
     * @Groups({"ArticleRead"})
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
     * @Groups({"ArticleRead"})
     */
    protected $createdAt;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->tags = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->ratings = new ArrayCollection();
        $this->commentsCount = 0;
        $this->published = false;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getImage(): ?Image
    {
        return $this->image;
    }

    public function setImage(?Image $image): void
    {
        $this->image = $image;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): void
    {
        $this->category = $category;
    }

    public function addTag(Tag $tag): void
    {
        $this->tags[] = $tag;
    }

    public function removeTag(Tag $tag): void
    {
        $this->tags->removeElement($tag);
    }

    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addComment(Comment $comment): void
    {
        $this->comments[] = $comment;
    }

    public function removeComment(Comment $comment): void
    {
        $this->comments->removeElement($comment);
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addRating(Rating $rating): void
    {
        $this->ratings[] = $rating;
    }

    public function removeRating(Rating $rating): void
    {
        $this->ratings->removeElement($rating);
    }

    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    public function getCommentsCount(): int
    {
        return $this->commentsCount;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setAuthor(?User $author): void
    {
        $this->author = $author;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function getPublishedAt(): ?DateTime
    {
        return $this->publishedAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): void
    {
        $this->published = $published;

        if ($this->published) {
            $this->publishedAt = new DateTime();
        }
    }

    public function isAuthor(?UserInterface $user): bool
    {
        $author = $this->getAuthor();

        return $author instanceof UserInterface && $author->isUser($user);
    }

    public function __toString(): string
    {
        return (string) $this->getCode();
    }

    /**
     * {@inheritdoc}
     */
    public function isThoughtSupported(ThoughtInterface $thought): bool
    {
        return $thought instanceof Comment || $thought instanceof Rating;
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedThoughts(): array
    {
        return [
            Comment::class,
            Rating::class,
        ];
    }
}
