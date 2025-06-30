<?php

namespace App\Entity;

use App\Repository\OutgoingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OutgoingRepository::class)]
class Outgoing
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTimeInterface $dateBegin = null;

    #[ORM\Column]
    private ?\DateInterval $duration = null;

    #[ORM\Column]
    private ?\DateTimeInterface $dateSubscriptionLimit = null;

    #[ORM\Column]
    private ?int $nbSubscriptionMax = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne]
    private ?Etat $etat = null;

    #[ORM\ManyToOne(inversedBy: 'organizedOutings')]
    private ?User $organizer = null;

    #[ORM\ManyToOne]
    private ?Site $site = null;

    #[ORM\ManyToOne]
    private ?Place $place = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'outings')]
    private Collection $participants;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getDateBegin(): ?\DateTimeInterface
    {
        return $this->dateBegin;
    }

    public function setDateBegin(?\DateTimeInterface $dateBegin): void
    {
        $this->dateBegin = $dateBegin;
    }

    public function getDuration(): ?\DateInterval
    {
        return $this->duration;
    }

    public function setDuration(?\DateInterval $duration): void
    {
        $this->duration = $duration;
    }

    public function getDateSubscriptionLimit(): ?\DateTimeInterface
    {
        return $this->dateSubscriptionLimit;
    }

    public function setDateSubscriptionLimit(?\DateTimeInterface $dateSubscriptionLimit): void
    {
        $this->dateSubscriptionLimit = $dateSubscriptionLimit;
    }

    public function getNbSubscriptionMax(): ?int
    {
        return $this->nbSubscriptionMax;
    }

    public function setNbSubscriptionMax(?int $nbSubscriptionMax): void
    {
        $this->nbSubscriptionMax = $nbSubscriptionMax;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): void
    {
        $this->etat = $etat;
    }

    public function getOrganizer(): ?User
    {
        return $this->organizer;
    }

    public function setOrganizer(?User $organizer): void
    {
        $this->organizer = $organizer;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): void
    {
        $this->site = $site;
    }

    public function getPlace(): ?Place
    {
        return $this->place;
    }

    public function setPlace(?Place $place): void
    {
        $this->place = $place;
    }

    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function setParticipants(Collection $participants): void
    {
        $this->participants = $participants;
    }


}
