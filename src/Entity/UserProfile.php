<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
class UserProfile
{
 #[ORM\Id]
 #[ORM\GeneratedValue]
 #[ORM\Column]
 private ?int $id = null;

 #[ORM\Column(type: 'string', length: 50, nullable: true)]
 private ?string $sex = null;

 #[ORM\Column(type: 'string', length: 100, nullable: true)]
 private ?string $situation = null;

 #[ORM\Column(type: Types::JSON, nullable: true)]
 private ?array $research = [];

 #[ORM\Column(type: 'text', nullable: true)]
 private ?string $biography = null;

 #[ORM\Column(type: 'string', length: 255, nullable: true)]
 private ?string $avatar = null;

 #[Assert\File(
  maxSize: '5M',
  mimeTypes: ['image/jpeg', 'image/png', 'image/gif'],
  mimeTypesMessage: 'Please upload a valid image file (JPEG, PNG, GIF)'
 )]
 private ?File $avatarFile = null;

 #[ORM\Column(type: 'json', nullable: true)]
 private ?array $photos = [];

 #[Assert\All([
   new Assert\File(
    maxSize: '5M',
    mimeTypes: ['image/jpeg', 'image/png', 'image/gif'],
    mimeTypesMessage: 'Each photo must be a valid image file (JPEG, PNG, GIF).'
   ),
  ])]
 private array $photoFiles = [];

 #[ORM\Column(type: 'string', length: 100, nullable: true)]
 private ?string $department = null;

 #[ORM\Column(type: 'string', length: 100, nullable: true)]
 private ?string $city = null;

 #[ORM\OneToOne(inversedBy: 'profile', targetEntity: User::class, cascade: ['persist', 'remove'])]
 #[ORM\JoinColumn(nullable: false)]
 private ?User $user = null;

 #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
#[Assert\NotBlank(message: 'La date de naissance est requise.')]
#[Assert\LessThanOrEqual(
    value: '-18 years',
    message: 'Vous devez avoir au moins 18 ans.'
)]
private ?\DateTimeInterface $birthdate = null;

#[ORM\OneToMany(mappedBy: 'liker', targetEntity: ProfileLike::class, cascade: ['remove'])]
private Collection $likesSent;

#[ORM\OneToMany(mappedBy: 'liked', targetEntity: ProfileLike::class, cascade: ['remove'])]
private Collection $likesReceived;

public function __construct()
{
    $this->likesSent = new ArrayCollection();
    $this->likesReceived = new ArrayCollection();

}

 public function getId(): ?int
 {return $this->id;}

 public function getSex(): ?string
 {return $this->sex;}

 public function setSex(?string $sex): static
 { $this->sex = $sex;return $this;}

 public function getSituation(): ?string
 {return $this->situation;}

 public function setSituation(?string $situation): static
 { $this->situation = $situation;return $this;}

 public function getResearch(): ?array
{
    return $this->research;
}

public function setResearch(?array $research): self
{
    $this->research = $research;

    return $this;
}

 public function getBiography(): ?string
 {return $this->biography;}

 public function setBiography(?string $biography): static
 { $this->biography = $biography;return $this;}

 public function getAvatar(): ?string
 {return $this->avatar;}
 public function setAvatar(?string $avatar): static
 { $this->avatar = $avatar;return $this;}

 public function getAvatarFile(): ?File
 {return $this->avatarFile;}
 public function setAvatarFile(?File $avatarFile): static
 { $this->avatarFile = $avatarFile;return $this;}

 public function getPhotos(): ?array
 {return $this->photos ?? [];}
 public function setPhotos(?array $photos): static
 { $this->photos = $photos;return $this;}

 public function getPhotoFiles(): array
 {return $this->photoFiles;}

 public function setPhotoFiles(array $photoFiles): static
 { $this->photoFiles = $photoFiles;return $this;}

 public function getDepartment(): ?string
 {return $this->department;}

 public function setDepartment(?string $department): static
 { $this->department = $department;return $this;}

 public function getCity(): ?string
 {return $this->city;}

 public function setCity(?string $city): static
 { $this->city = $city;return $this;}

 public function getUser(): ?User
 {return $this->user;}

 public function setUser(User $user): self
 {$this->user = $user;return $this;}

 public function removePhoto(string $photoFilename): void
 {
  // Si la photo existe dans le tableau
  if (in_array($photoFilename, $this->photos)) {
   // Retirer la photo du tableau
   $this->photos = array_diff($this->photos, [$photoFilename]);

   // Supprimer le fichier photo du serveur
   $photoPath = $this->getUploadDir() . '/' . $photoFilename;
   if (file_exists($photoPath)) {
    unlink($photoPath);
   }
  }
 }

 public function getUploadDir(): string
 {
  return __DIR__ . '/../../public/uploads/photos'; // Adapté selon ton environnement
 }

 public function getBirthdate(): ?\DateTimeInterface
{
    return $this->birthdate;
}

public function setBirthdate(?\DateTimeInterface $birthdate): static
{
    $this->birthdate = $birthdate;
    return $this;
}

/**
 * @return Collection|ProfileLike[]
 */
public function getLikesSent(): Collection
{
    return $this->likesSent;
}

// Getters pour les likes reçus
/**
 * @return Collection|ProfileLike[]
 */
public function getLikesReceived(): Collection
{
    return $this->likesReceived;
}

}
