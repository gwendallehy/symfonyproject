<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['pseudo'], message: 'Ce pseudo est déjà utilisé.')]
#[UniqueEntity(fields: ['email'], message: 'Cette adresse e-mail est déjà utilisée.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Le pseudo est obligatoire.")]
    #[Assert\Length(
        min: 3,
        max: 180,
        minMessage: "Le pseudo doit faire au moins {{ limit }} caractères.",
        maxMessage: "Le pseudo ne peut pas dépasser {{ limit }} caractères."
    )]
    #[ORM\Column(length: 180, unique: true)]
    private string $pseudo;

    #[ORM\Column]
    private array $roles = [];

    #[Assert\NotBlank(message: "Le mot de passe est obligatoire.")]
    #[Assert\Length(min: 6)]
    #[ORM\Column]
    private string $password;

    #[Assert\NotBlank(message: "Le prénom est obligatoire.")]
    #[Assert\Length(max: 100)]
    #[ORM\Column(length: 100)]
    private string $firstname;

    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    #[Assert\Length(max: 100)]
    #[ORM\Column(length: 100)]
    private string $lastname;

    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas valide.")]
    #[ORM\Column(length: 100)]
    private string $email;

    #[Assert\NotBlank(message: "Le téléphone est obligatoire.")]
    #[Assert\Length(max: 20)]
    #[Assert\Regex(
        pattern: "/^\+?[0-9\s\-]+$/",
        message: "Le numéro de téléphone '{{ value }}' n'est pas valide."
    )]
    #[ORM\Column(length: 20)]
    private string $phone;

    #[ORM\Column]
    private bool $administrator;

    #[ORM\Column]
    private bool $active;

    #[Assert\Url(message: "L'URL de la photo '{{ value }}' n'est pas valide.")]
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $picture = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?Site $site = null;

    #[ORM\OneToMany(
        targetEntity: Outgoing::class,
        mappedBy: 'organizer',
        cascade: ['remove'],
        orphanRemoval: true
    )]
    private Collection $organizedOutings;

    #[ORM\ManyToMany(targetEntity: Outgoing::class, mappedBy: 'participants')]
    private Collection $outings;

    public function __construct()
    {
        $this->organizedOutings = new ArrayCollection();
        $this->outings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;
        return  $this;
    }

    public function getPseudo(): string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;
        return  $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return  $this;
    }
    public function getUserIdentifier(): string
    {
        return $this->pseudo;
    }
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return  $this;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;
        return  $this;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;
        return  $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return  $this;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;
        return  $this;
    }

    public function isAdministrator(): bool
    {
        return $this->administrator;
    }

    public function setAdministrator(bool $administrator): static
    {
        $this->administrator = $administrator;
        return  $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;
        return  $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): static
    {
        $this->picture = $picture;
        return  $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): static
    {
        $this->site = $site;
        return  $this;
    }

    public function getOrganizedOutings(): Collection
    {
        return $this->organizedOutings;
    }

    public function setOrganizedOutings(Collection $organizedOutings): static
    {
        $this->organizedOutings = $organizedOutings;
        return  $this;
    }

    public function getOutings(): Collection
    {
        return $this->outings;
    }

    public function setOutings(Collection $outings): static
    {
        $this->outings = $outings;
        return  $this;
    }


    public function eraseCredentials(): void
    {
        // TODO: Implement eraseCredentials() method.
    }
}
