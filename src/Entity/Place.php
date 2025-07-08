<?php

namespace App\Entity;

use App\Repository\PlaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity(repositoryClass: PlaceRepository::class)]
class Place
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    #[Assert\Length(max: 100)]
    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[Assert\NotBlank(message: "La rue est obligatoire.")]
    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255)]
    private ?string $street = null;

    #[Assert\NotNull(message: "La latitude est obligatoire.")]
    #[Assert\Range(
        notInRangeMessage: "La latitude doit être comprise entre {{ min }} et {{ max }}.",
        min: -90,
        max: 90
    )]
    #[ORM\Column]
    private ?float $latitude = null;

    #[Assert\NotNull(message: "La longitude est obligatoire.")]
    #[Assert\Range(
        notInRangeMessage: "La longitude doit être comprise entre {{ min }} et {{ max }}.",
        min: -180,
        max: 180
    )]
    #[ORM\Column]
    private ?float $longitude = null;

    #[ORM\ManyToOne]
    private ?City $city = null;

    #[ORM\OneToMany(targetEntity: Outgoing::class, mappedBy: 'place')]
    private Collection $outings;

    public function __construct()
    {
        $this->outings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): static
    {
        $this->street = $street;
        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): static
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): static
    {
        $this->longitude = $longitude;
        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function getOutings(): Collection
    {
        return $this->outings;
    }

    public function setOutings(Collection $outings): static
    {
        $this->outings = $outings;
        return $this;
    }

}
