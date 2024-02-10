<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\AuthorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Survos\ApiGrid\State\MeiliSearchStateProvider;
use Survos\CoreBundle\Entity\RouteParametersInterface;
use Survos\CoreBundle\Entity\RouteParametersTrait;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity(repositoryClass: AuthorRepository::class)]
#[ApiResource(
    // normal get is the database
    operations: [new Get(), new GetCollection(
        name: 'api_authors', extraProperties: ['alias' => 'author_list']
    )],
    normalizationContext: ['groups' => ['author.read', 'rp']]
)]
//#[GetCollection(
//    uriTemplate: "meili/{indexName}",
//    uriVariables: ["indexName"],
//    provider: MeiliSearchStateProvider::class,
//    normalizationContext: [
//        'groups' => ['author.read', 'tree', 'rp'],
//    ]
//)]
#[GetCollection(
    name: 'author_meili',
    uriTemplate: "meili/Author",
//    uriVariables: ["indexName"],
    provider: MeiliSearchStateProvider::class,
    extraProperties: [
        'indexName' => 'AuthorIndex'
    ],
    normalizationContext: [
        'groups' => ['article.read', 'tree', 'rp'],
    ]
)]
#[ApiFilter(OrderFilter::class, properties: ['id', 'fullName', 'articleCount'])]

#[Assert\EnableAutoMapping]
#[Groups(['author.read'])]
class Author implements RouteParametersInterface
{
    use RouteParametersTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['article.read', 'author.read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::GUID)]
    #[Groups(['article.read', 'author.read'])]
    private ?string $uuid = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['article.read', 'author.read'])]
    private ?string $avatar = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['article.read', 'author.read'])]
    private ?string $profile = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['article.read', 'author.read'])]
    private ?string $fullName = null;

    #[ORM\ManyToMany(targetEntity: Article::class, inversedBy: 'authors')]
    private Collection $articles;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): static
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getProfile(): ?string
    {
        return $this->profile;
    }

    public function setProfile(?string $profile): static
    {
        $this->profile = $profile;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(?string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    #[Groups(['author.read'])]
    public function getArticleCount(): int
    {
        return $this->getArticles()->count();
    }

    public function addArticle(Article $article): static
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
        }

        return $this;
    }

    public function removeArticle(Article $article): static
    {
        $this->articles->removeElement($article);

        return $this;
    }
}
