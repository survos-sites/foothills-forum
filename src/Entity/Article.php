<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Put;
use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Survos\ApiGrid\Api\Filter\FacetsFieldSearchFilter;
use Survos\ApiGrid\Api\Filter\MultiFieldSearchFilter;
use Survos\ApiGrid\Attribute\Facet;
use Survos\ApiGrid\State\MeiliSearchStateProvider;
use Survos\CoreBundle\Entity\RouteParametersInterface;
use Survos\CoreBundle\Entity\RouteParametersTrait;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use function Symfony\Component\String\u;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ApiResource(
    shortName: 'article',
    operations: [new Get(), new Put(), new Delete(), new Patch(),
        new GetCollection(
            provider: MeiliSearchStateProvider::class,
        )],
    normalizationContext: [
        'groups' => ['article.read', 'rp'],
    ]
)]
//#[GetCollection(
//    uriTemplate: "meili/{indexName}",
//    uriVariables: ["indexName"],
//    provider: MeiliSearchStateProvider::class,
//    normalizationContext: [
//        'groups' => ['article.read', 'tree', 'rp'],
//    ]
//)]

// keywords and sections are arrays, so fail with getCounts() if doctrine, okay if meili
#[ApiFilter(FacetsFieldSearchFilter::class, properties: ['section', 'byline', 'tags', 'keywords', 'sections'])] // ,'sections','keywords'])]
//#[ApiFilter(FacetsFieldSearchFilter::class, properties: ['section', 'byline'])] // ,'sections','keywords'])]
#[ApiFilter(MultiFieldSearchFilter::class, properties: ['headline', 'subheadline'])]
#[ApiFilter(OrderFilter::class, properties: ['id',
    'byline',
    'section',
])]


#[Groups(['article.read'])]
#[Assert\EnableAutoMapping]
class Article implements RouteParametersInterface
{
    use RouteParametersTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $headline = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $url = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Facet()]
    private ?string $byline = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Facet()]
    private ?string $section = null;

    #[ORM\Column(type: Types::GUID)]
    private $uuid;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
//    #[Assert\Length(max: 255)]
    private ?string $subheadline = null;

    #[ORM\ManyToMany(targetEntity: Author::class, mappedBy: 'articles')]
    private Collection $authors;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['jsonb' => true])]
    #[Groups(['article.read'])]
    private ?array $sections = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['jsonb' => true])]
    #[Groups(['article.read'])]
    private ?array $keywords = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['jsonb' => true])]
    #[Groups(['article.read'])]
    private ?array $tags = null;

    public function __construct()
    {
        $this->authors = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getHeadline(): ?string
    {
        return $this->headline;
    }

    public function setHeadline(string $headline): static
    {
        $this->headline = $headline;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getByline(): ?string
    {
        return $this->byline;
    }

    public function setByline(?string $byline): static
    {
        $this->byline = $byline;

        return $this;
    }

    public function getSection(): ?string
    {
        return $this->section;
    }

    public function setSection(string $section): static
    {
        assert(strlen($section) <= 255, $section);
        $this->section = $section;

        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getSubheadline(): ?string
    {
        return $this->subheadline;
    }

    public function setSubheadline(?string $subheadline): static
    {
        $this->subheadline = $subheadline;

        return $this;
    }

    /**
     * @return Collection<int, Author>
     */
    #[Groups(['article.read'])]
    public function getAuthors(): Collection
    {
        return $this->authors;
    }

    public function addAuthor(Author $author): static
    {
        if (!$this->authors->contains($author)) {
            $this->authors->add($author);
            $author->addArticle($this);
        }

        return $this;
    }

    public function removeAuthor(Author $author): static
    {
        if ($this->authors->removeElement($author)) {
            $author->removeArticle($this);
        }

        return $this;
    }

    public function getSections(): ?array
    {
        return $this->sections;
    }

    public function setSections(?array $sections): self
    {
        $this->sections = $sections;

        return $this;
    }

    public function getKeywords(): ?array
    {
        return $this->keywords;
    }

    public function setKeywords(?array $keywords): self
    {
        $this->keywords = $keywords;
//                if (count($keywords)) dd($keywords);

        return $this;
    }

    public function getUniqueIdentifiers(): array
    {
        return ['articleId' => $this->getUuid()];
    }

    #[Groups(['article.read'])]

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function setTags(?array $tags): self
    {
        $this->tags = [];
        foreach ($tags as $tag) {
            if (!preg_match('/foothills|hurl|sperryville/', $tag)) continue;
            $this->tags[] = $tag;
//            $this->tags[] = str_replace(' ', '_', $tag);
        }

        return $this;
    }
}
