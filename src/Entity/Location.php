<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\LocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Survos\ApiGrid\Api\Filter\FacetsFieldSearchFilter;
use Survos\ApiGrid\State\MeiliSearchStateProvider;
use Survos\CoreBundle\Entity\RouteParametersInterface;
use Survos\CoreBundle\Entity\RouteParametersTrait;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
#[ApiResource(
    // normal get is the database
    operations: [new Get(), new GetCollection(
        name: self::MEILI_ROUTE,
        provider: MeiliSearchStateProvider::class,
    )],
    normalizationContext: ['groups' => ['location.read', 'rp']]
)]

// keywords and sections are arrays, so fail with getCounts() if doctrine, okay if meili
#[ApiFilter(FacetsFieldSearchFilter::class, properties: ['school'])]
//#[ApiFilter(MultiFieldSearchFilter::class, properties: ['headline', 'subheadline'])]
#[ApiFilter(OrderFilter::class,
    properties: ['id',
        'name',
        'school'
    ])]

#[Groups('location.read')]
class Location implements RouteParametersInterface, \Stringable
{
    use RouteParametersTrait;

    const MEILI_ROUTE='meili-locations';

    #[ORM\Id]
    #[ORM\GeneratedValue('SEQUENCE')]
    #[ORM\Column]
    #[ApiProperty(identifier: false)]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups('location.read')]
    #[ApiProperty(identifier: true)]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Groups(['location.read', 'submission.read'])]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'location', targetEntity: Event::class, orphanRemoval: true)]
    private Collection $events;

    #[ORM\ManyToOne(inversedBy: 'locations')]
    #[ORM\JoinColumn(nullable: true)]
    private ?School $school = null;

    #[ORM\OneToMany(mappedBy: 'location', targetEntity: Submission::class)]
    private Collection $submissions;

    public function __construct(?string $code=null)
    {
        if ($code) {
            $this->setCode($code);
        }
        $this->events = new ArrayCollection();
        $this->submissions = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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
            $event->setLocation($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getLocation() === $this) {
                $event->setLocation(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getCode();
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

    /**
     * @return Collection<int, Submission>
     */
    public function getSubmissions(): Collection
    {
        return $this->submissions;
    }

    public function addSubmission(Submission $submission): static
    {
        if (!$this->submissions->contains($submission)) {
            $this->submissions->add($submission);
            $submission->setLocation($this);
        }

        return $this;
    }

    public function removeSubmission(Submission $submission): static
    {
        if ($this->submissions->removeElement($submission)) {
            // set the owning side to null (unless already changed)
            if ($submission->getLocation() === $this) {
                $submission->setLocation(null);
            }
        }

        return $this;
    }

    public function getUniqueIdentifiers(): array
    {
        return ['locationId' => $this->getCode()];
    }


}
