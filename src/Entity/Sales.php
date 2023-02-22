<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SalesRepository;
use App\Validator as MyAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SalesRepository::class)]
#[MyAssert\MaxProductSales]
class Sales
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?float $price = null;

    #[ORM\ManyToOne(inversedBy: 'sales')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToMany(targetEntity: CompanyContact::class, inversedBy: 'sales')]
    private Collection $contact;

    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 4, nullable: true)]
    private ?float $percent_vr = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 4, nullable: true)]
    private ?float $percent_com = null;

    #[ORM\ManyToOne(inversedBy: 'sales')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(nullable: false)]
    private int $quantity = 1;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2, nullable: true)]
    private ?float $discount = null;

    private ?float $discount_eur;
    private ?float $discount_percent;
    private ?float $pa;

    public function __construct()
    {
        $this->contact = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrice(): ?float
    {
        return (int) $this->price;
    }

    public function getTotalPrice(): ?float
    {
        if (0 === $this->getQuantity()) {
            return (int) $this->price;
        }

        return (int) $this->price * $this->getQuantity();
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;

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

    /**
     * @return Collection<int, CompanyContact>
     */
    public function getContact(): Collection
    {
        return $this->contact;
    }

    public function addContact(CompanyContact $contact): self
    {
        if (!$this->contact->contains($contact)) {
            $this->contact->add($contact);
        }

        return $this;
    }

    public function removeContact(CompanyContact $contact): self
    {
        $this->contact->removeElement($contact);

        return $this;
    }

    public function getPercentVr(): ?float
    {
        return $this->percent_vr;
    }

    public function getEuroVr(): float
    {
        return $this->getMarge() * $this->getPercentVr() / 100;
    }

    public function getEuroCom(): float
    {
        return $this->getMarge() * $this->getPercentCom() / 100;
    }

    public function getMarge(): float
    {
        return $this->getTotalPrice() - $this->product->getPa() * $this->quantity - $this->getDiscount();
    }

    public function getDiffCa(): float
    {
        return $this->getMarge() - $this->getEuroCom() - $this->getEuroVr();
    }

    public function getPa(): float
    {
        return $this->product->getPa();
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

    public function getDiscount(): ?float
    {
        return $this->discount;
    }

    public function setDiscount(?float $discount): self
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
}
