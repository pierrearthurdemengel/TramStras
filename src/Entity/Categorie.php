<?php

namespace App\Entity;

use App\Repository\CategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: CategorieRepository::class)]
class Categorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    public ?string $nom_categorie = null;

    #[ORM\OneToMany(mappedBy: 'categorie', targetEntity: Topic::class)]
    private Collection $topics;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $dataCard = null;

    public function __construct()
    {
        $this->topics = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomCategorie(): ?string
    {
        return $this->nom_categorie;
    }

    public function setNomCategorie(string $nom_categorie): self
    {
        $this->nom_categorie = $nom_categorie;

        return $this;
    }

    /**
     * @return Collection<int, Topic>
     */
    public function getTopics(): Collection
    {
        return $this->topics;
    }

    public function addTopic(Topic $topic): self
    {
        if (!$this->topics->contains($topic)) {
            $this->topics->add($topic);
            $topic->setCategorie($this);
        }

        return $this;
    }

    public function removeTopic(Topic $topic): self
    {
        if ($this->topics->removeElement($topic)) {
            if ($topic->getCategorie() === $this) {
                $topic->setCategorie(null);
            }
        }

        return $this;
    }

    public function getDataCard(): ?string
    {
        return $this->dataCard;
    }

    public function setDataCard(?string $dataCard): self
    {
        $this->dataCard = $dataCard;

        return $this;
    }


    public function __toString(): string
    {
        return $this->nom_categorie;
    }
}
