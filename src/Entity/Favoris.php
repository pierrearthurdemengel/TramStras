<?php

namespace App\Entity;

use App\Repository\FavorisRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FavorisRepository::class)]
class Favoris
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $arret = null;

    #[ORM\Column(length: 255)]
    private ?string $sens = null;

    #[ORM\Column(length: 255)]
    private ?string $ligne = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArret(): ?string
    {
        return $this->arret;
    }

    public function setArret(string $arret): self
    {
        $this->arret = $arret;

        return $this;
    }

    public function getSens(): ?string
    {
        return $this->sens;
    }

    public function setSens(string $sens): self
    {
        $this->sens = $sens;

        return $this;
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
}
