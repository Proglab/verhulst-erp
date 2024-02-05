<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SalesRepository;
use App\Validator as MyAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SalesRepository::class)]
#[MyAssert\MaxProductSales]
class Sales
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 11)]
    #[Assert\PositiveOrZero]
    private ?string $price = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 11)]
    #[Assert\PositiveOrZero]
    private ?string $pa = '0';

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    #[Assert\Length(max: 6)]
    #[Assert\PositiveOrZero]
    private ?string $percent_vr = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 4, scale: 2, nullable: true)]
    #[Assert\Length(max: 5)]
    #[Assert\PositiveOrZero]
    private ?string $percent_com = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(nullable: false)]
    #[Assert\Positive]
    private int $quantity = 1;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2, nullable: true)]
    #[Assert\Length(max: 9)]
    #[Assert\PositiveOrZero]
    private ?string $discount = '0.0';

    #[Assert\PositiveOrZero]
    private ?float $discount_eur;
    #[Assert\PositiveOrZero]
    private ?float $discount_percent;

    #[ORM\Column]
    private ?bool $invoiced = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $invoiced_dt = null;

    #[ORM\ManyToOne(inversedBy: 'sales')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'sales')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private ?Product $product = null;

    #[ORM\ManyToOne(inversedBy: 'sales')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private ?CompanyContact $contact = null;

    #[ORM\ManyToMany(targetEntity: SalesBdc::class, mappedBy: 'sales')]
    private Collection $salesBdcs;

    public function __construct()
    {
        $this->salesBdcs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

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

    public function getDiscount(): float
    {
        if (null === $this->discount) {
            return 0;
        }

        return (float) $this->discount;
    }

    public function setDiscount(float $discount): self
    {
        $this->discount = (string) $discount;

        return $this;
    }

    public function getDiscountEur(): ?float
    {
        return $this->discount_eur;
    }

    public function setDiscountEur(?float $discount): self
    {
        $this->discount_eur = $discount;

        return $this;
    }

    public function getDiscountPercent(): ?float
    {
        return $this->discount_percent;
    }

    public function setDiscountPercent(?float $discount): self
    {
        $this->discount_percent = $discount;

        return $this;
    }

    public function getDiffCa(): float
    {
        return $this->getMarge() - $this->getEuroCom() - $this->getEuroVr() - ($this->getPa() * $this->getQuantity());
    }

    public function getTotalPrice(): ?float
    {
        if (0 === $this->getQuantity()) {
            return (int) $this->price;
        }

        return (int) $this->price * $this->getQuantity();
    }

    public function getEuroVr(): float
    {
        if (empty($this->getPercentVr())) {
            return 0.0;
        }

        return $this->getMarge() * ($this->getPercentVr() - $this->getPercentCom()) / 100;
    }

    public function getEuroCom(): float
    {
        return $this->getMarge() * $this->getPercentCom() / 100;
    }

    public function getMarge(): float
    {
        return $this->getTotalPrice() - $this->getDiscount();
    }

    public function isInvoiced(): ?bool
    {
        return $this->invoiced;
    }

    public function setInvoiced(bool $invoiced): self
    {
        $this->invoiced = $invoiced;

        return $this;
    }

    public function getInvoicedDt(): ?\DateTimeInterface
    {
        return $this->invoiced_dt;
    }

    public function setInvoicedDt(?\DateTimeInterface $invoiced_dt): self
    {
        $this->invoiced_dt = $invoiced_dt;

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

    public function getNet(): float
    {
        return $this->getDiffCa() + $this->getEuroVr();
    }

    /**
     * @return Collection<int, SalesBdc>
     */
    public function getSalesBdcs(): Collection
    {
        return $this->salesBdcs;
    }

    public function addSalesBdc(SalesBdc $salesBdc): static
    {
        if (!$this->salesBdcs->contains($salesBdc)) {
            $this->salesBdcs->add($salesBdc);
            $salesBdc->addSale($this);
        }

        return $this;
    }

    public function removeSalesBdc(SalesBdc $salesBdc): static
    {
        if ($this->salesBdcs->removeElement($salesBdc)) {
            $salesBdc->removeSale($this);
        }

        return $this;
    }
}
