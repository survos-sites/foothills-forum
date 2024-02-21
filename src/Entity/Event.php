<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Survos\ApiGrid\Api\Filter\FacetsFieldSearchFilter;
use Survos\ApiGrid\Api\Filter\MultiFieldSearchFilter;
use Survos\ApiGrid\State\MeiliSearchStateProvider;
use Survos\CoreBundle\Entity\RouteParametersInterface;
use Survos\CoreBundle\Entity\RouteParametersTrait;
use Symfony\Component\Serializer\Annotation\Groups;
use function Symfony\Component\String\u;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ApiResource(
    // normal get is the database
    operations: [new Get(), new GetCollection(
        name: self::MEILI_ROUTE,
        provider: MeiliSearchStateProvider::class,
    )],
    normalizationContext: ['groups' => ['event.read', 'rp']]
)]

// keywords and sections are arrays, so fail with getCounts() if doctrine, okay if meili
#[ApiFilter(FacetsFieldSearchFilter::class, properties: ['sportCode', 'opponent','location','section'])]
//#[ApiFilter(MultiFieldSearchFilter::class, properties: ['headline', 'subheadline'])]
#[ApiFilter(OrderFilter::class,
    properties: ['id',
    'submissionCount',
        'eventDate'
])]

#[Groups('event.read')]
class Event implements RouteParametersInterface, \Stringable
{
    use RouteParametersTrait;
    public const MEILI_ROUTE='meili_events';

    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    #[ApiProperty(identifier: false)]
    private ?int $id = null;

    /**
     * @param int|null $id
     */
    public function __construct(?string $code=null)
    {
        if ($code) {
            $this->setCode($code);
        }
        $this->submissions = new ArrayCollection();
    }


    public function setId(?int $id): Event
    {
        $this->id = $id;
        $this->code = 'rsc-' . $id;
        return $this;
    }

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups('event.read')]
    private ?\DateTimeInterface $eventDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('event.read')]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('event.read')]
    private ?string $opponent = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('event.read')]
    private ?string $score = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $summary = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('event.read')]
    private ?string $section = null;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: Submission::class, orphanRemoval: true)]
    private Collection $submissions;

    #[ORM\Column(length: 255)]
    #[ApiProperty(identifier: true)]
    private ?string $code = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sport $sport = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    #[Groups('event.read')]
    private ?int $rSchoolId = null;

    #[ORM\Column(nullable: true)]
    private ?int $submissionCount = 0;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups('event.read')]
    private ?Location $location = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEventDate(): ?\DateTimeInterface
    {
        return $this->eventDate;
    }

    public function setEventDate(?\DateTimeInterface $eventDate): static
    {
        $this->eventDate = $eventDate;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getOpponent(): ?string
    {
        return $this->opponent;
    }

    public function setOpponent(?string $opponent): static
    {
        $this->opponent = $opponent;

        return $this;
    }

    public function getScore(): ?string
    {
        return $this->score;
    }

    public function setScore(?string $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): static
    {
        $this->summary = $summary;

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

    public function getUniqueIdentifiers(): array
    {
        return ['eventId' => $this->getCode()];
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
            $submission->setEvent($this);
            $this->submissionCount++;
        }

        return $this;
    }

    public function removeSubmission(Submission $submission): static
    {
        if ($this->submissions->removeElement($submission)) {
            // set the owning side to null (unless already changed)
            if ($submission->getEvent() === $this) {
                $submission->setEvent(null);
            }
            $this->submissionCount--;
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getTitle();
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

    public function getSport(): ?Sport
    {
        return $this->sport;
    }

    public function setSport(?Sport $sport): static
    {
        $this->sport = $sport;

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

    public function getRSchoolId(): ?int
    {
        return $this->rSchoolId;
    }

    public function setRSchoolId(?int $rSchoolId): static
    {
        $this->rSchoolId = $rSchoolId;

        return $this;
    }

    public function getSubmissionCount(): ?int
    {
        return $this->submissionCount;
    }

    public function setSubmissionCount(?int $submissionCount): static
    {
        $this->submissionCount = $submissionCount;

        return $this;
    }

    #[Groups(['event.read'])]
    public function getSportCode()
    {
        return $this->getSport()->getCode();

    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getTitle()
    {
        return sprintf("%s %s %s", $this->getSection(), u($this->getSport())->title(), $this->getEventDate()->format('D M d, Y h:iA'));

    }


}
