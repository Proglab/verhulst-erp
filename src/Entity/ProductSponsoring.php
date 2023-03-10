<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProductSponsoringRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductSponsoringRepository::class)]
class ProductSponsoring extends Product
{
    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'product_sponsoring')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\Length(max: 11)]
    #[Assert\PositiveOrZero]
    private ?float $ca = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Assert\PositiveOrZero]
    private ?int $quantity_max = null;

    public function __toString()
    {
        return $this->getProject()->getName() . ' - ' . $this->getName();
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

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

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
}
