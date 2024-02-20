<?php

namespace App\Entity;

use App\Entity\ImagesUsers;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà associé à un compte')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['alerte_read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: 'boolean')]
    private $isVerified = false;

    #[ORM\Column(length: 50)]
    #[Groups(['alerte_read'])]
    private ?string $pseudo = null;
    
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Post::class)]
    private Collection $Post;
    
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Topic::class)]
    private Collection $Topic;
    
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Marker::class, orphanRemoval: true)]
    private Collection $markers;
    
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Subscription::class, orphanRemoval: true)]
    private Collection $subscriptions;
    
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripeId = null;
    
    #[ORM\OneToOne(inversedBy: 'user', targetEntity: ImagesUsers::class, cascade: ['persist', 'remove'])]
    #[Groups(['alerte_read'])]
    private ?ImagesUsers $imagesUsers = null;

    public function __construct()
    {
        $this->Post = new ArrayCollection();
        $this->Topic = new ArrayCollection();
        $this->markers = new ArrayCollection();
        $this->subscriptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function __toString(): string
    {
        $roles = implode(', ', $this->roles);
        
        // Remplacer le rôle "ROLE_ADMIN" par "admin"
        if (in_array('ROLE_ADMIN', $this->roles)) {
            $roles = str_replace('ROLE_ADMIN', 'admin', $roles);
        }
        
        // Filtrer les rôles pour ne pas afficher "ROLE_USER"
        $filteredRoles = array_filter($this->roles, fn($role) => $role !== 'ROLE_USER');
        
        return $this->email.' '.$this->pseudo.' '.implode(', ', $filteredRoles).' '.$this->isVerified;
    }
    

    /**
     * @return Collection<int, Post>
     */
    public function getPost(): Collection
    {
        return $this->Post;
    }

    public function addPost(Post $post): self
    {
        if (!$this->Post->contains($post)) {
            $this->Post->add($post);
            $post->setUser($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->Post->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getUser() === $this) {
                $post->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Topic>
     */
    public function getTopic(): Collection
    {
        return $this->Topic;
    }

    public function addTopic(Topic $topic): self
    {
        if (!$this->Topic->contains($topic)) {
            $this->Topic->add($topic);
            $topic->setUser($this);
        }

        return $this;
    }

    public function removeTopic(Topic $topic): self
    {
        if ($this->Topic->removeElement($topic)) {
            // set the owning side to null (unless already changed)
            if ($topic->getUser() === $this) {
                $topic->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Marker>
     */
    public function getMarkers(): Collection
    {
        return $this->markers;
    }

    public function addMarker(Marker $marker): self
    {
        if (!$this->markers->contains($marker)) {
            $this->markers->add($marker);
            $marker->setUser($this);
        }

        return $this;
    }

    public function removeMarker(Marker $marker): self
    {
        if ($this->markers->removeElement($marker)) {
            // set the owning side to null (unless already changed)
            if ($marker->getUser() === $this) {
                $marker->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Subscription>
     */
    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }

    public function addSubscription(Subscription $subscription): self
    {
        if (!$this->subscriptions->contains($subscription)) {
            $this->subscriptions->add($subscription);
            $subscription->setUser($this);
        }

        return $this;
    }

    public function removeSubscription(Subscription $subscription): self
    {
        if ($this->subscriptions->removeElement($subscription)) {

            if ($subscription->getUser() === $this) {
                $subscription->setUser(null);
            }
        }

        return $this;
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

    public function getImagesUsers(): ?ImagesUsers
    {
        return $this->imagesUsers;
    }

    public function setImagesUsers(ImagesUsers $imagesUsers): self
    {
        $this->imagesUsers = $imagesUsers;

        return $this;
    }

    // Evite le Problème de serialization avec VichUploadBundle lors de la moidification de l'image de profile
        public function serialize()
    {
        return serialize([
            $this->id,
            $this->email,
            $this->password,
            $this->pseudo,
            $this->isVerified,
            $this->roles
        ]);
    }

    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->email,
            $this->password,
            $this->pseudo,
            $this->isVerified,
            $this->roles
        ) = unserialize($serialized, ['allowed_classes' => false]);
    }


}

