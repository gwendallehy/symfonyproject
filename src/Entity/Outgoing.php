<?php

namespace App\Entity;

use App\Repository\EtatRepository;
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

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateBegin = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateSubscriptionLimit = null;

    #[ORM\Column(type: 'integer')]
    private ?int $duration = null;

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

    public function getDateBegin(): ?\DateTimeInterface
    {
        return $this->dateBegin;
    }

    public function setDateBegin(?\DateTimeInterface $dateBegin): static
    {
        $this->dateBegin = $dateBegin;
        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): static
    {
        $this->duration = $duration;
        return $this;
    }

    public function isSubscribed(User $user): bool
    {
        return $this->participants->contains($user);
    }


    public function getDateSubscriptionLimit(): ?\DateTimeInterface
    {
        return $this->dateSubscriptionLimit;
    }

    public function setDateSubscriptionLimit(?\DateTimeInterface $dateSubscriptionLimit): static
    {
        $this->dateSubscriptionLimit = $dateSubscriptionLimit;
        return $this;
    }

    public function getNbSubscriptionMax(): ?int
    {
        return $this->nbSubscriptionMax;
    }

    public function setNbSubscriptionMax(?int $nbSubscriptionMax): static
    {
        $this->nbSubscriptionMax = $nbSubscriptionMax;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): static
    {
        $this->etat = $etat;
        return $this;
    }

    public function getOrganizer(): ?User
    {
        return $this->organizer;
    }

    public function setOrganizer(?User $organizer): static
    {
        $this->organizer = $organizer;
        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): static
    {
        $this->site = $site;
        return $this;
    }

    public function getPlace(): ?Place
    {
        return $this->place;
    }

    public function setPlace(?Place $place): static
    {
        $this->place = $place;
        return $this;
    }

    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function setParticipants(Collection $participants): static
    {
        $this->participants = $participants;
        return $this;
    }

    public function addParticipant(User $user): static
    {
        if (!$this->participants->contains($user)) {
            $this->participants->add($user);
        }
        return $this;
    }

    public function removeParticipant(User $user): static
    {
        $this->participants->removeElement($user);
        return $this;
    }

// ... (méthodes suivantes)
    public function isOpenForSubscription(): bool
    {
        return $this->getEtat()->getLibelle() === 'Ouverte'
            && new \DateTime() < $this->getDateSubscriptionLimit()
            && count($this->getParticipants()) < $this->getNbSubscriptionMax();
    }

    public function isFull(): bool
    {
        if ($this->getParticipants()->count() > $this->getNbSubscriptionMax()) {
            return true;
        }
        return false;
    }

    public function updateEtat(EtatRepository $etatRepository): void
    {
        $now = new \DateTime();

        // Si l'état est Annulée, on ne change rien
        if ($this->getEtat()->getLibelle() === 'Annulée') {
            return;
        }

        // Inscription ouverte
        if ($now <= $this->getDateSubscriptionLimit()) {
            $etat = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
            $this->setEtat($etat);
            return;
        }

        // Inscription clôturée, activité pas encore commencée
        if ($now > $this->getDateSubscriptionLimit() && $now < $this->getDateBegin()) {
            $etat = $etatRepository->findOneBy(['libelle' => 'Clôturée']);
            $this->setEtat($etat);
            return;
        }

        // Activité en cours
        if ($now >= $this->getDateBegin() && $now <= $this->getDateEnd()) {
            $etat = $etatRepository->findOneBy(['libelle' => 'Activité en cours']);
            $this->setEtat($etat);
            return;
        }

        // Activité passée
        if ($now > $this->getDateEnd()) {
            $etat = $etatRepository->findOneBy(['libelle' => 'Passée']);
            $this->setEtat($etat);
            return;
        }
    }


    public function hasStarted(): bool
    {
        return new \DateTime() >= $this->getDateBegin();
    }

    public function getDateEnd(): ?\DateTimeInterface
    {
        if (!$this->dateBegin || !$this->duration) {
            return null; // ou lever une exception si nécessaire
        }

        $dateEnd = clone $this->dateBegin;
        $dateEnd->modify('+' . $this->duration . ' minutes');

        return $dateEnd;
    }



}
