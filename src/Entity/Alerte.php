<?php

namespace App\Entity;

use App\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\AlerteRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AlerteRepository::class)]
class Alerte
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['alerte_read'])]
    private ?string $ligne = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['alerte_read'])]
    private ?\DateTimeInterface $alerteDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['alerte_read'])]
    private ?string $sens = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Groups(['alerte_read'])]
    private ?User $user = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLigne(): ?string
    {
        return $this->ligne;
    }

    public function setLigne(string $ligne): self
    {
        $this->ligne = $ligne;

        return $this;
    }

    public function getAlerteDate(): ?\DateTimeInterface
    {
        return $this->alerteDate;
    }

    public function setAlerteDate(\DateTimeInterface $alerteDate): self
    {
        $this->alerteDate = $alerteDate;

        return $this;
    }

    public function getSens(): ?string
    {
        return $this->sens;
    }

    public function setSens(?string $sens): self
    {
        $this->sens = $sens;

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
}