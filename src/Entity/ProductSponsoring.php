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
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Assert\PositiveOrZero]
    private ?int $quantity_max = null;

    public function __toString()
    {
        return $this->getName();
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

    public function getQuantityAvailable(): ?int
    {
        if (null !== $this->getQuantityMax() && $this->getQuantityMax() > 0) {
            return $this->getQuantityMax() - $this->getQuantitySales();
        }

        return null;
    }
}
