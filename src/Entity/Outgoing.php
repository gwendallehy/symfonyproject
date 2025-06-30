<?php

namespace App\Entity;

use App\Repository\OutgoingRepository;
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
    private ?\DateTime $dateBegin = null;

    #[ORM\Column]
    private ?\DateInterval $duration = null;

    #[ORM\Column]
    private ?\DateTime $dateSubscriptionLimit = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $nbSubscriptionMax = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descrpition = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $state = null;

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

    public function getDateBegin(): ?\DateTime
    {
        return $this->dateBegin;
    }

    public function setDateBegin(\DateTime $dateBegin): static
    {
        $this->dateBegin = $dateBegin;

        return $this;
    }

    public function getDuration(): ?\DateInterval
    {
        return $this->duration;
    }

    public function setDuration(\DateInterval $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDateSubscriptionLimit(): ?\DateTime
    {
        return $this->dateSubscriptionLimit;
    }

    public function setDateSubscriptionLimit(\DateTime $dateSubscriptionLimit): static
    {
        $this->dateSubscriptionLimit = $dateSubscriptionLimit;

        return $this;
    }

    public function getNbSubscriptionMax(): ?int
    {
        return $this->nbSubscriptionMax;
    }

    public function setNbSubscriptionMax(int $nbSubscriptionMax): static
    {
        $this->nbSubscriptionMax = $nbSubscriptionMax;

        return $this;
    }

    public function getDescrpition(): ?string
    {
        return $this->descrpition;
    }

    public function setDescrpition(?string $descrpition): static
    {
        $this->descrpition = $descrpition;

        return $this;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(int $state): static
    {
        $this->state = $state;

        return $this;
    }
}
