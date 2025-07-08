<?php

namespace App\Entity;

use App\Repository\CityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity(repositoryClass: CityRepository::class)]
class City
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le nom de la ville est obligatoire.")]
    #[Assert\Length(
        max: 100,
        maxMessage: "Le nom de la ville ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $name = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Le code postal est obligatoire.")]
    #[Assert\Range(
        notInRangeMessage: "Le code postal doit être compris entre {{ min }} et {{ max }}.",
        min: 1000,
        max: 99999
    )]
    private ?int $postalCode = null;

    #[ORM\OneToMany(targetEntity: Place::class, mappedBy: 'city')]
    private Collection $places;

    public function __construct()
    {
        $this->places = new ArrayCollection();
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

    public function getPostalCode(): ?int
    {
        return $this->postalCode;
    }

    public function setPostalCode(?int $postalCode): static
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    public function getPlaces(): Collection
    {
        return $this->places;
    }

    public function setPlaces(Collection $places): static
    {
        $this->places = $places;
        return $this;
    }
}
