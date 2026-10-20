<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string>
     */
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 80)]
    private ?string $pseudo = null;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: UserProfile::class, cascade: ['persist', 'remove'])]
    private ?UserProfile $profile = null;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: Subscription::class, cascade: ['persist', 'remove'])]
    private ?Subscription $subscription = null;

    // ===== Reset password =====
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $resetToken = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $resetTokenExpiresAt = null;

    public function getProfile(): ?UserProfile
    {
        return $this->profile;
    }

    public function setProfile(?UserProfile $profile): self
    {
        $this->profile = $profile;

        if ($profile && $profile->getUser() !== $this) {
            $profile->setUser($this);
        }

        return $this;
    }

    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }

    public function setSubscription(?Subscription $subscription): static
    {
        $this->subscription = $subscription;

        if ($subscription && $subscription->getUser() !== $this) {
            $subscription->setUser($this);
        }

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        // A Premium role linked to a subscription is valid only while that
        // subscription is active and has not reached its end date.
        if ($this->subscription !== null
            && (!$this->subscription->isActive()
                || ($this->subscription->getEndDate() !== null
                    && $this->subscription->getEndDate() <= new \DateTimeImmutable()))) {
            $roles = array_values(array_filter(
                $roles,
                static fn (string $role): bool => $role !== 'ROLE_PREMIUM'
            ));
        }

        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->roles, true);
    }

    public function addRole(string $role): static
    {
        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }
        return $this;
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void {}

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;
        return $this;
    }

    // ===== Reset password getters/setters =====
    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;
        return $this;
    }

    public function getResetTokenExpiresAt(): ?\DateTimeImmutable
    {
        return $this->resetTokenExpiresAt;
    }

    public function setResetTokenExpiresAt(?\DateTimeImmutable $date): self
    {
        $this->resetTokenExpiresAt = $date;
        return $this;
    }

    // ===== PROXYS pour Twig (app.user.sexe / situation / bio / etc.) =====
    public function getSexe(): ?string
    {
        return $this->profile?->getSex();
    }

    public function getSituation(): ?string
    {
        return $this->profile?->getSituation();
    }

    public function getBio(): ?string
    {
        return $this->profile?->getBiography();
    }

    public function getBiography(): ?string
    {
        return $this->profile?->getBiography();
    }

    public function getAge(): ?int
    {
        return $this->profile?->getAge();
    }

    public function getCity(): ?string
    {
        return $this->profile?->getCity();
    }

    public function getDepartment(): ?string
    {
        return $this->profile?->getDepartment();
    }

    public function getResearch(): ?array
    {
        return $this->profile?->getResearch();
    }

    public function getAvatar(): ?string
    {
        return $this->profile?->getAvatar();
    }

    public function getPhotos(): ?array
    {
        return $this->profile?->getPhotos();
    }
}
