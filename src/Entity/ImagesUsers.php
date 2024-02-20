<?php

namespace App\Entity;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ThumbnailRepository;
use App\Repository\ImagesUsersRepository;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;


#[ORM\Entity(repositoryClass: \App\Repository\ImagesUsersRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Vich\Uploadable]
class ImagesUsers
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    // Image en elle même
    #[Vich\UploadableField(mapping: 'images_users', fileNameProperty: 'imageName')]
    private ?File $imageFile = null;

    // Nom de l'image
    #[ORM\Column(type: 'string')]
    private ?string $imageName = null;

    #[ORM\Column(type: 'integer')]
    private ?int $imageSize = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\OneToOne(mappedBy: 'imagesUsers', targetEntity: User::class)]
    private User $user;


    public function __construct()
    {
        $this->updatedAt = new \DateTimeImmutable();
        $this->createdAt = new \DateTimeImmutable();

    }

    // public function __sleep()
    // {
    //     return ['id', 'imageName', 'imageSize', 'updatedAt', 'createdAt'];
    // }
    

    #[ORM\PreUpdate]
    public function preUpdate()
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageSize(?int $imageSize): void
    {
        $this->imageSize = $imageSize;
    }

    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser($user): self
    {
        $this->user = $user;

        return $this;
    }

}
