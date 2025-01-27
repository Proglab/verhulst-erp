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
class Sales extends BaseSales
{
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

    #[ORM\ManyToMany(targetEntity: SalesBdc::class, mappedBy: 'sales')]
    private Collection $salesBdcs;

    public function __construct()
    {
        $this->salesBdcs = new ArrayCollection();
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

    public function isInvoiced(): ?bool
    {
        return $this->invoiced;
    }

    public function setInvoiced(?bool $invoiced): self
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
