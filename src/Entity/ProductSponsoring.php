<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProductSponsoringRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductSponsoringRepository::class)]
class ProductSponsoring extends Product
{
    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'product_sponsoring')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?float $ca = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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
}
