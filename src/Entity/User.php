<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\CreatedAtTrait;
use App\Entity\Trait\EnabledTrait;
use App\Entity\Trait\PrimaryKeyTrait;
use App\Entity\Trait\VerifiedTrait;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Slug;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfigurationInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, EquatableInterface, TwoFactorInterface
{
    use CreatedAtTrait;
    use EnabledTrait;
    use PrimaryKeyTrait;
    use VerifiedTrait;

    final public const ROLE_ADMIN = 'ROLE_ADMIN';
    final public const ROLE_COMMERCIAL = 'ROLE_COMMERCIAL';
    final public const ROLE_USER = 'ROLE_USER';
    final public const ROLE_BOSS = 'ROLE_BOSS';

    #[ORM\Column(type: Types::STRING, length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email(message: 'Veuillez renseigner un email valide')]
    #[Assert\Length(max: 180)]
    private ?string $email = null;

    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    #[ORM\Column(type: Types::STRING)]
    private ?string $password = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\Length(min: 1, max: 20, minMessage: 'Le prénom doit contenir au moins 1 caractère', maxMessage: 'Le prénom doit contenir au plus 20 caractères')]
    #[Assert\NotBlank(message: 'Veuillez renseigner un prénom')]
    private ?string $firstName = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\Length(min: 1, max: 20, minMessage: 'Le nom doit contenir au plus 20 caractères')]
    #[Assert\NotBlank(message: 'Veuillez renseigner un nom')]
    private ?string $lastName = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Slug(fields: ['lastName', 'firstName'])]
    private ?string $slug = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ResendConfirmationEmailRequest::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $resendConfirmationEmailRequests;

    #[ORM\Column(type: Types::STRING, length: 2)]
    #[Assert\NotBlank]
    #[Assert\Locale]
    private ?string $locale = 'fr';

    #[ORM\Column(name: 'totpSecret', type: Types::STRING, nullable: true)]
    private ?string $totpSecret = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isTotpEnabled = false;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Sales::class, orphanRemoval: true)]
    private Collection $sales;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Commission::class, orphanRemoval: true)]
    private Collection $commissions;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->enabled = false;
        $this->resendConfirmationEmailRequests = new ArrayCollection();
        $this->sales = new ArrayCollection();
        $this->commissions = new ArrayCollection();
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setFullNameCharacteristics(): void
    {
        if ($this->lastName !== ucfirst(strtolower($this->lastName))) {
            $this->lastName = ucfirst(strtolower($this->lastName));
        }
        if ($this->firstName !== ucfirst(strtolower($this->firstName))) {
            $this->firstName = ucfirst(strtolower($this->firstName));
        }
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
    public function getUsername(): string
    {
        return (string) $this->email;
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

    public function addRole(string $role): self
    {
        if (!\in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function hasRole(string $role): bool
    {
        return \in_array(strtoupper($role), $this->getRoles(), true);
    }

    public function removeRole(string $role): self
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

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
    public function eraseCredentials(): void
    {
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getResendConfirmationEmailRequests(): Collection
    {
        return $this->resendConfirmationEmailRequests;
    }

    public function addResendConfirmationEmailRequest(ResendConfirmationEmailRequest $resendConfirmationEmailRequest): self
    {
        if (!$this->resendConfirmationEmailRequests->contains($resendConfirmationEmailRequest)) {
            $this->resendConfirmationEmailRequests[] = $resendConfirmationEmailRequest;
            $resendConfirmationEmailRequest->setUser($this);
        }

        return $this;
    }

    public function removeResendConfirmationEmailRequest(ResendConfirmationEmailRequest $resendConfirmationEmailRequest): self
    {
        // set the owning side to null (unless already changed)
        if ($this->resendConfirmationEmailRequests->removeElement($resendConfirmationEmailRequest) && $resendConfirmationEmailRequest->getUser() === $this) {
            $resendConfirmationEmailRequest->setUser(null);
        }

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function isTotpAuthenticationEnabled(): bool
    {
        return (true === $this->isTotpEnabled) && (null !== $this->totpSecret);
    }

    public function getTotpAuthenticationUsername(): string
    {
        return $this->getUserIdentifier();
    }

    public function getTotpAuthenticationConfiguration(): ?TotpConfigurationInterface
    {
        return new TotpConfiguration($this->totpSecret, TotpConfiguration::ALGORITHM_SHA1, 30, 6);
    }

    public function setTotpSecret(?string $totpSecret): self
    {
        $this->totpSecret = $totpSecret;

        return $this;
    }

    public function isTotpEnabled(): bool
    {
        return $this->isTotpEnabled;
    }

    public function setIsTotpEnabled(bool $isTotpEnabled): self
    {
        $this->isTotpEnabled = $isTotpEnabled;

        return $this;
    }

    public function isEqualTo(UserInterface $user): bool
    {
        return !(false === $user->getEnabled() && $user->getVerified());
    }

    public function getFullname(): string
    {
        return $this->lastName . ' ' . $this->firstName;
    }

    /**
     * @return Collection<int, Sales>
     */
    public function getSales(): Collection
    {
        return $this->sales;
    }

    public function addSale(Sales $sale): self
    {
        if (!$this->sales->contains($sale)) {
            $this->sales->add($sale);
            $sale->setUser($this);
        }

        return $this;
    }

    public function removeSale(Sales $sale): self
    {
        if ($this->sales->removeElement($sale)) {
            // set the owning side to null (unless already changed)
            if ($sale->getUser() === $this) {
                $sale->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Commission>
     */
    public function getCommissions(): Collection
    {
        return $this->commissions;
    }

    public function addCommission(Commission $commission): self
    {
        if (!$this->commissions->contains($commission)) {
            $this->commissions->add($commission);
            $commission->setUser($this);
        }

        return $this;
    }

    public function removeCommission(Commission $commission): self
    {
        if ($this->commissions->removeElement($commission)) {
            // set the owning side to null (unless already changed)
            if ($commission->getUser() === $this) {
                $commission->setUser(null);
            }
        }

        return $this;
    }
}
