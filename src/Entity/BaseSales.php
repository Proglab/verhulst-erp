<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\BaseSalesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BaseSalesRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap(['normal' => Sales::class, 'fast' => FastSales::class])]
#[ORM\Table(name: 'sales')]
class BaseSales
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $name = null;

    /**
     * @var string|null $po Numéro de PO
     */
    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $po = null;

    /**
     * @var string|null $price Prix de vente
     */
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\Length(max: 11)]
    protected ?string $price = null;

    /**
     * @var string|null $forecast_price Prix de vente prévisionnel
     */
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\Length(max: 11)]
    #[Assert\PositiveOrZero]
    protected ?string $forecast_price = null;

    /**
     * @var string|null $pa Prix d'achat
     */
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 11)]
    #[Assert\PositiveOrZero]
    protected ?string $pa = '0';

    /**
     * @var string|null $percent_vr % de commission VR
     */
    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: false)]
    #[Assert\Length(max: 6)]
    #[Assert\PositiveOrZero]
    protected ?string $percent_vr = null;

    /**
     * @var string|null $percent_vr_eur commission VR en euros
     */
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\Length(max: 11)]
    protected ?string $percent_vr_eur = null;

    /**
     * @var string $percent_com_type Type de commission (sales)
     */
    #[ORM\Column(nullable: false)]
    #[Assert\Choice(choices: ['percent_com', 'percent_pv', 'fixed'])]
    protected string $percent_com_type = '';

    /**
     * @var string $percent_vr_type Type de commission (Verhulst)
     */
    #[ORM\Column(nullable: false)]
    #[Assert\Choice(choices: ['percent', 'fixed'])]
    protected string $percent_vr_type = '';

    /**
     * @var string|null $percent_com % de commission sales
     */
    #[ORM\Column(type: Types::DECIMAL, precision: 4, scale: 2, nullable: false)]
    #[Assert\Length(max: 5)]
    #[Assert\PositiveOrZero]
    protected ?string $percent_com = null;

    /**
     * @var string|null $percent_com_eur commission sales en euros
     */
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\Length(max: 11)]
    protected ?string $percent_com_eur = null;

    #[ORM\Column(nullable: false)]
    #[Assert\Positive]
    protected int $quantity = 1;

    /**
     * @var \DateTimeInterface|null $date date d'encodage
     */
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    protected ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(inversedBy: 'sales')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    protected ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'sales')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    protected ?CompanyContact $contact = null;

    #[ORM\ManyToOne(inversedBy: 'sales')]
    private ?Product $product = null;

    public function totalPrice(): float
    {
        return $this->getPrice() * $this->getQuantity();
    }

    public function totalForecastPrice(): float
    {
        return $this->getForecastPrice() * $this->getQuantity();
    }

    public function totalPa(): float
    {
        switch ($this->percent_vr_type) {
            case 'percent':
                return $this->totalPrice() - ($this->totalPrice() * $this->getPercentVr() / 100);
            case 'fixed':
                return (float) $this->getPercentComEur() * $this->getQuantity();
            default:
                return (float) $this->getPercentComEur() * $this->getQuantity();
        }
    }

    public function totalVr(): float
    {
        switch ($this->percent_vr_type) {
            case 'percent':
                return $this->totalPrice() * $this->getPercentVr() / 100;
            case 'fixed':
                return (float) $this->getPercentVrEur() * $this->getQuantity();
            default:
                return (float) $this->totalPrice() * $this->getPercentVr() / 100;
        }
    }

    public function totalCom(): float
    {
        switch ($this->percent_com_type) {
            case 'percent_com':
                return $this->totalPrice() * $this->getPercentCom() / 100;
            case 'percent_vr':
                return $this->totalVr() * $this->getPercentCom() / 100;
            case 'fixed':
                return (float) $this->getPercentComEur() * $this->getQuantity();
            default:
        }

        return $this->totalPrice() * $this->getPercentCom() / 100;
    }

    public function totalVrNet(): float
    {
        return $this->totalVr() - $this->totalCom();
    }

    public function marge(): float
    {
        return $this->totalPrice() - $this->totalPa() - $this->totalVr();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPo(): ?string
    {
        return $this->po;
    }

    public function setPo(?string $po): self
    {
        $this->po = $po;

        return $this;
    }

    public function getPrice(): ?float
    {
        return (float) $this->price;
    }

    public function setPrice(string|float|null $price): self
    {
        $this->price = (string) str_replace(',', '.', (string) $price);

        return $this;
    }

    public function getPa(): ?float
    {
        return (float) $this->pa;
    }

    public function setPa(string|float|null $pa): self
    {
        $this->pa = (string) str_replace(',', '.', (string) $pa);

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

    public function getPercentVr(): ?float
    {
        return (float) $this->percent_vr;
    }

    public function setPercentVr(?float $percent_vr): self
    {
        $this->percent_vr = (string) $percent_vr;

        return $this;
    }

    public function getPercentCom(): ?float
    {
        return (float) $this->percent_com;
    }

    public function setPercentCom(?float $percent_com): self
    {
        $this->percent_com = (string) $percent_com;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getContact(): ?CompanyContact
    {
        return $this->contact;
    }

    public function setContact(?CompanyContact $contact): self
    {
        $this->contact = $contact;

        return $this;
    }

    public function getForecastPrice(): ?float
    {
        return (float) $this->forecast_price;
    }

    public function setForecastPrice(string|float|null $forecastPrice): self
    {
        $this->forecast_price = (string) str_replace(',', '.', (string) $forecastPrice);

        return $this;
    }

    public function getPercentVrEur(): ?string
    {
        return $this->percent_vr_eur;
    }

    public function setPercentVrEur(?string $percent_vr_eur): void
    {
        $this->percent_vr_eur = (string) str_replace(',', '.', (string) $percent_vr_eur);
    }

    public function getPercentComType(): string
    {
        return $this->percent_com_type;
    }

    public function setPercentComType(string $percent_com_type): void
    {
        $this->percent_com_type = $percent_com_type;
    }

    public function getPercentComEur(): ?string
    {
        return $this->percent_com_eur;
    }

    public function setPercentComEur(?string $percent_com_eur): void
    {
        $this->percent_com_eur = (string) str_replace(',', '.', (string) $percent_com_eur);
    }

    public function getPercentVrType(): string
    {
        return $this->percent_vr_type;
    }

    public function setPercentVrType(string $percent_vr_type): void
    {
        $this->percent_vr_type = $percent_vr_type;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }
}
