<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Survos\ApiGrid\Api\Filter\FacetsFieldSearchFilter;
use Survos\ApiGrid\State\MeiliSearchStateProvider;
use Survos\CoreBundle\Entity\RouteParametersInterface;
use Survos\CoreBundle\Entity\RouteParametersTrait;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
#[ApiResource(
    // normal get is the database
    operations: [new Get(), new GetCollection(
        name: self::MEILI_ROUTE,
        provider: MeiliSearchStateProvider::class,
    )],
    normalizationContext: ['groups' => ['team.read', 'rp']]
)]
#[ApiFilter(FacetsFieldSearchFilter::class, properties: ['school','section','sport'])]
//#[ApiFilter(MultiFieldSearchFilter::class, properties: ['headline', 'subheadline'])]
#[ApiFilter(OrderFilter::class,
    properties: ['id',
        'code'
    ])]

class Team implements RouteParametersInterface, \Stringable
{
    use RouteParametersTrait;

    const MEILI_ROUTE='meili_team';
    #[ORM\Id]
    #[ORM\GeneratedValue('SEQUENCE')]
    #[ORM\Column]
    #[Groups(['team.read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['team.read'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[ApiProperty(identifier: true)]
    #[Groups(['team.read'])]
    private ?string $code = null;

    #[ORM\ManyToOne(inversedBy: 'teams')]
    #[ORM\JoinColumn(nullable: false)]
    private ?School $school = null;

    #[ORM\ManyToOne(inversedBy: 'teams')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['team.read'])]
    private ?Sport $sport = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['team.read'])]
    private ?string $section = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['event.read','team.read'])]
    private ?int $rSchoolId = null;

    #[ORM\OneToMany(mappedBy: 'team', targetEntity: Event::class)]
    private Collection $events;

    public function __construct(?string $code=null)
    {
        if ($code) {
            $this->setCode($code);
        }
        $this->events = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        // hack!
        $name = str_replace('rappahannockcountyhs','rchs', $name);
        $this->name = $name;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): static
    {
        $this->school = $school;

        return $this;
    }

    public function getSport(): ?Sport
    {
        return $this->sport;
    }

    public function setSport(?Sport $sport): static
    {
        $this->sport = $sport;

        return $this;
    }

    public function getSection(): ?string
    {
        return $this->section;
    }

    public function setSection(?string $section): static
    {
        $this->section = $section;

        return $this;
    }

    public function __toString()
    {
        return $this->getCode();
    }

    public function getRSchoolId(): ?int
    {
        return $this->rSchoolId;
    }

    public function setRSchoolId(?int $rSchoolId): static
    {
        $this->rSchoolId = $rSchoolId;

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setTeam($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getTeam() === $this) {
                $event->setTeam(null);
            }
        }

        return $this;
    }

    #[Groups(['team.read'])]
    public function getEventCount()
    {
        return $this->getEvents()->count();

    }

}
