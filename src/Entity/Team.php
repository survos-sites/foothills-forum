<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Survos\CoreBundle\Entity\RouteParametersInterface;
use Survos\CoreBundle\Entity\RouteParametersTrait;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
#[ApiResource]
class Team implements RouteParametersInterface, \Stringable
{
    use RouteParametersTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[ApiProperty(identifier: true)]
    private ?string $code = null;

    #[ORM\ManyToOne(inversedBy: 'teams')]
    #[ORM\JoinColumn(nullable: false)]
    private ?School $school = null;

    #[ORM\ManyToOne(inversedBy: 'teams')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sport $sport = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $section = null;

    public function __construct(?string $code=null)
    {
        if ($code) {
            $this->setCode($code);
        }
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

}
