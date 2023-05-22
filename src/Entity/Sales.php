<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SalesRepository;
use App\Validator as MyAssert;
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
    private ?float $price = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 11)]
    #[Assert\PositiveOrZero]
    private ?float $pa = 0;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    #[Assert\Length(max: 6)]
    #[Assert\PositiveOrZero]
    private ?float $percent_vr = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 4, scale: 2, nullable: true)]
    #[Assert\Length(max: 5)]
    #[Assert\PositiveOrZero]
    private ?float $percent_com = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(nullable: false)]
    #[Assert\Positive]
    private int $quantity = 1;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2, nullable: true)]
    #[Assert\Length(max: 9)]
    #[Assert\PositiveOrZero]
    private ?float $discount = 0.0;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrice(): ?float
    {
        return (int) $this->price;
    }

    public function setPrice(string|float|null $price): self
    {
        $this->price = (float) str_replace(',', '.', (string) $price);

        return $this;
    }

    public function getPa(): ?float
    {
        return (int) $this->pa;
    }

    public function setPa(string|float|null $pa): self
    {
        $this->pa = (float) str_replace(',', '.', (string) $pa);

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
        return $this->percent_vr;
    }

    public function setPercentVr(?float $percent_vr): self
    {
        $this->percent_vr = $percent_vr;

        return $this;
    }

    public function getPercentCom(): ?float
    {
        return $this->percent_com;
    }

    public function setPercentCom(?float $percent_com): self
    {
        $this->percent_com = $percent_com;

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

        return $this->discount;
    }

    public function setDiscount(float $discount): self
    {
        $this->discount = $discount;

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
        return $this->getMarge() - $this->getEuroCom() - $this->getEuroVr();
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
}
