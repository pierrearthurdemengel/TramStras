<?php

namespace App\Entity;

use App\Repository\SubscriptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubscriptionRepository::class)]
class Subscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $stripeId = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $currentPeriodStart = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $currentPeriodEnd = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Plan $plan = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStripeId(): ?string
    {
        return $this->stripeId;
    }

    public function setStripeId(string $stripeId): self
    {
        $this->stripeId = $stripeId;

        return $this;
    }

    public function getCurrentPeriodStart(): ?\DateTimeInterface
    {
        return $this->currentPeriodStart;
    }

    public function setCurrentPeriodStart(\DateTimeInterface $currentPeriodStart): self
    {
        $this->currentPeriodStart = $currentPeriodStart;

        return $this;
    }

    public function getCurrentPeriodEnd(): ?\DateTimeInterface
    {
        return $this->currentPeriodEnd;
    }

    public function setCurrentPeriodEnd(\DateTimeInterface $currentPeriodEnd): self
    {
        $this->currentPeriodEnd = $currentPeriodEnd;

        return $this;
    }

    public function isIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getPlan(): ?Plan
    {
        return $this->plan;
    }

    public function setPlan(?Plan $plan): self
    {
        $this->plan = $plan;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }


}
