<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\UserInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An article, such as a news article or piece of investigative report. Newspapers and magazines have articles of many different types and this is intended to cover them all.\\n\\nSee also \[blog post\](http://blog.schema.org/2014/09/schemaorg-support-for-bibliographic\_2.html).
 *
 * @see http://schema.org/Article Documentation on Schema.org
 *
 * @ApiResource(iri="http://schema.org/Article",
 * attributes={
 *   "access_control"="is_granted('ROLE_READER')",
 * })
 *
 * @ORM\Entity()
 * @ORM\Table(name="articles")
 */
class Article
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
     * @ORM\Column(type="string", unique=true)
     *
     * @Gedmo\Slug(fields={"createdAt", "title"},separator="-",updatable=true,unique=true,dateFormat="Y/m")
     */
    protected $code;

    /**
     * @var string|null the name of the item
     *
     * @ORM\Column(type="string", nullable=false)
     *
     * @ApiProperty(iri="http://schema.org/name")
     *
     * @Assert\NotBlank()
     */
    protected $title;

    /**
     * @var string|null the actual body of the article
     *
     * @ORM\Column(type="text")
     *
     * @ApiProperty(iri="http://schema.org/articleBody")
     *
     * @Assert\NotBlank()
     */
    protected $body;

    /**
     * @var string[]|null articles may belong to one or more 'sections' in a magazine or newspaper, such as Sports, Lifestyle, etc
     *
     * @ORM\Column(type="array")
     *
     * @ApiProperty(iri="http://schema.org/articleSection")
     */
    protected $sections;

    /**
     * @var string|null the subject matter of the content
     *
     * @ORM\Column(type="text")
     *
     * @ApiProperty(iri="http://schema.org/about")
     *
     * @Assert\NotBlank()
     */
    protected $about;

    /**
     * @var \App\Entity\User The author of this content or rating. Please note that author is special in that HTML 5 provides a special mechanism for indicating authorship via the rel tag. That is equivalent to this and may be used interchangeably.
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id")
     *
     * @ApiProperty(iri="http://schema.org/author")
     *
     * @Assert\NotBlank()
     */
    protected $author;

    /**
     * @var \DateTimeInterface|null date of first broadcast/publication
     *
     * @ORM\Column(type="datetime",nullable=true)
     *
     * @ApiProperty(iri="http://schema.org/datePublished")
     *
     * @Assert\DateTime()
     *
     * @Gedmo\Timestampable(on="change",field="published",value="true")
     */
    protected $publishedAt;

    /**
     * @var bool determines whether article was published
     *
     * @ORM\Column(type="boolean")
     */
    protected $published;

    /**
     * @var \DateTimeInterface|null the date on which the CreativeWork was most recently modified or when the item's entry was modified within a DataFeed
     *
     * @ORM\Column(type="datetime")
     *
     * @ApiProperty(iri="http://schema.org/dateModified")
     *
     * @Gedmo\Timestampable(on="update")
     *
     * @Assert\DateTime()
     */
    protected $updatedAt;

    /**
     * @var \DateTimeInterface|null the date on which the CreativeWork was created or the item was added to a DataFeed
     *
     * @ORM\Column(type="datetime")
     *
     * @ApiProperty(iri="http://schema.org/dateCreated")
     *
     * @Gedmo\Timestampable(on="create")
     *
     * @Assert\DateTime()
     */
    protected $createdAt;


    public function __construct()
    {
        $this->sections = new ArrayCollection();
        $this->published = false;
    }


    /**
     * @return null|\Ramsey\Uuid\Uuid
     */
    public function getId(): ?Uuid
    {
        return $this->id;
    }


    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }


    public function setBody(?string $body): void
    {
        $this->body = $body;
    }


    public function getBody(): ?string
    {
        return $this->body;
    }


    public function addSection(string $section): void
    {
        $this->sections[] = $section;
    }


    public function removeSection(string $section): void
    {
        $this->sections->removeElement($section);
    }


    public function getSections(): Collection
    {
        return $this->sections;
    }


    public function setAbout(?string $about): void
    {
        $this->about = $about;
    }


    public function getAbout(): ?string
    {
        return $this->about;
    }


    public function setAuthor(?User $author): void
    {
        $this->author = $author;
    }


    public function getAuthor(): ?User
    {
        return $this->author;
    }


    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }


    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }


    public function getCreatedAt(): ?\DateTimeInterface
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


    /**
     * @return bool
     */
    public function isPublished(): bool
    {
        return $this->published;
    }


    /**
     * @param bool $published
     *
     * @return Article
     */
    public function setPublished($published): Article
    {
        $this->published = (bool) $published;

        return $this;
    }


    /**
     * @param UserInterface|null $user
     *
     * @return bool
     */
    public function isAuthor(?UserInterface $user): bool
    {
        $author = $this->getAuthor();

        if (!$author instanceof User) {
            return false;
        }

        return $author->isUser($user);
    }
}
