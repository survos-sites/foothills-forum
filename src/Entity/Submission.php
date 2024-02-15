<?php

namespace App\Entity;

use App\Repository\SubmissionRepository;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
#[ORM\Entity(repositoryClass: SubmissionRepository::class)]
#[Vich\Uploadable]
class Submission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
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
