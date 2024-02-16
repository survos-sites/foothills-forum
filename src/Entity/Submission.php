<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\SubmissionRepository;
use Survos\ApiGrid\Api\Filter\FacetsFieldSearchFilter;
use Survos\ApiGrid\Api\Filter\MultiFieldSearchFilter;
use Survos\CoreBundle\Entity\RouteParametersInterface;
use Survos\CoreBundle\Entity\RouteParametersTrait;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
#[ORM\Entity(repositoryClass: SubmissionRepository::class)]
#[Vich\Uploadable]
#[ApiResource(
    shortName: 'article',
    operations: [new Get(),
        new GetCollection(name: 'doctrine-articles')],
    normalizationContext: [
        'groups' => ['submission.read', 'rp'],
    ]
)]
// keywords and sections are arrays, so fail with getCounts() if doctrine, okay if meili
//#[ApiFilter(FacetsFieldSearchFilter::class, properties: [''])] // ,'sections','keywords'])]
//#[ApiFilter(FacetsFieldSearchFilter::class, properties: ['section', 'byline'])] // ,'sections','keywords'])]
//#[ApiFilter(MultiFieldSearchFilter::class, properties: ['headline', 'subheadline'])]
#[ApiFilter(OrderFilter::class, properties: ['id',
    'id',
])]

class Submission implements RouteParametersInterface
{
    use RouteParametersTrait;
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

    public function getImageFile(): ?File
    {
        return $this->imageFile;
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
}
