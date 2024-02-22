<?php

// see https://blog.theodo.com/2013/11/dynamic-mapping-in-doctrine-and-symfony-how-to-extend-entities/ for re-use
namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\SubmissionRepository;
use Doctrine\DBAL\Types\Types;
use Survos\ApiGrid\Api\Filter\FacetsFieldSearchFilter;
use Survos\ApiGrid\Api\Filter\MultiFieldSearchFilter;
use Survos\CoreBundle\Entity\RouteParametersInterface;
use Survos\CoreBundle\Entity\RouteParametersTrait;
use Survos\WorkflowBundle\Attribute\Transition;
use Survos\WorkflowBundle\Attribute\Workflow;
use Survos\WorkflowBundle\Traits\MarkingInterface;
use Survos\WorkflowBundle\Traits\MarkingTrait;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
#[ORM\Entity(repositoryClass: SubmissionRepository::class)]
#[Vich\Uploadable]
#[ApiResource(
    shortName: 'submission',
    operations: [new Get(),
        new GetCollection(name: self::DOCTRINE_ROUTE)],
    normalizationContext: [
        'groups' => ['submission.read', 'rp', 'marking'],
    ]
)]
// keywords and sections are arrays, so fail with getCounts() if doctrine, okay if meili
#[ApiFilter(FacetsFieldSearchFilter::class, properties: ['marking'])] // ,'sections','keywords'])]
//#[ApiFilter(FacetsFieldSearchFilter::class, properties: ['section', 'byline'])] // ,'sections','keywords'])]
//#[ApiFilter(MultiFieldSearchFilter::class, properties: ['headline', 'subheadline'])]
#[ApiFilter(OrderFilter::class, properties: ['id',
    'marking',
    'id',
])]

#[Workflow('PLACE_')]
class Submission implements RouteParametersInterface, MarkingInterface, \Stringable
{
    const PLACE_NEW='new';
    const PLACE_APPROVED='approved';
    const PLACE_REJECTED='rejected';
    #[Transition(from:[self::PLACE_NEW], to: self::PLACE_APPROVED)]
    const TRANSITION_APPROVE='approve';
    #[Transition(from:[self::PLACE_NEW], to: self::PLACE_REJECTED)]
    const TRANSITION_REJECT='reject';

    #[Transition(from:[self::PLACE_REJECTED, self::PLACE_APPROVED], to: self::PLACE_NEW)]
    const TRANSITION_RESET='reset';

    const MEILI_ROUTE='';
    const DOCTRINE_ROUTE='doctrine-submission';
    use RouteParametersTrait;
    use MarkingTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('submission.read')]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('submission.read')]
    private ?string $imageName = null;

    #[ORM\Column(nullable: true)]
    private ?int $imageSize = null;

    // NOTE: This is not a mapped field of entity metadata, just a simple property.
    #[Vich\UploadableField(mapping: 'submissions', fileNameProperty: 'imageName', size: 'imageSize')]
    private ?File $imageFile = null;

    #[ORM\ManyToOne(inversedBy: 'submissions')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups('submission.read')]
    private ?Event $event = null;

    #[ORM\ManyToOne(inversedBy: 'submissions')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Location $location = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('submission.read')]
    private ?string $credit = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('submission.read')]
    private ?string $email = null;

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getFlowCode(): string
    {
        return 'Submission';
    }

    public function setImageFile(?File $imageFile): Submission
    {
        $this->imageFile = $imageFile;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): static
    {
        $this->imageName = $imageName;

        return $this;
    }

    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }

    public function setImageSize(?int $imageSize): static
    {
        $this->imageSize = $imageSize;

        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getUniqueIdentifiers(): array
    {
        return ['submissionId' => $this->getId()];
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

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getCredit(): ?string
    {
        return $this->credit;
    }

    public function setCredit(?string $credit): static
    {
        $this->credit = $credit;

        return $this;
    }


    public function __toString()
    {
        return $this->getEvent()->getTitle() . '#' . $this->getId();
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getCode()
    {
        return sprintf("photo-%d", $this->getId());
    }
}
