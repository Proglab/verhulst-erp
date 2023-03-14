<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProductPackageVipRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductPackageVipRepository::class)]
class ProductPackageVip extends Product
{
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\Length(max: 11)]
    #[Assert\PositiveOrZero]
    private ?float $ca = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Assert\PositiveOrZero]
    private ?int $quantity_max = null;

    public function __toString()
    {
        return $this->getProject()->getName() . ' - ' . $this->getName() . ' ' . $this->getCa() . ' EUR';
    }

    public function getCa(): ?float
    {
        return $this->ca;
    }

    public function setCa(?float $ca): self
    {
        $this->ca = $ca;

        return $this;
    }

    public function getQuantityMax(): ?int
    {
        return $this->quantity_max;
    }

    public function setQuantityMax(?int $quantity_max): self
    {
        $this->quantity_max = $quantity_max;

        return $this;
    }

    public function getQuantitySales(): int
    {
        $quantity = 0;
        foreach ($this->getSales() as $sale) {
            $quantity += $sale->getQuantity();
        }

        return $quantity;
    }

    public function getQuantityAvailable(): ?int
    {
        if (null !== $this->getQuantityMax() && $this->getQuantityMax() > 0) {
            return $this->getQuantityMax() - $this->getQuantitySales();
        }

        return null;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->getDateBegin();
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        parent::setDateBegin($date);

        return $this;
    }

    public function setDateBegin(?\DateTimeInterface $date): self
    {
        parent::setDateBegin($date);
        parent::setDateEnd($date);

        return $this;
    }
}
