<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProductPackageVipRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductPackageVipRepository::class)]
class ProductPackageVip extends Product
{
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $ca = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'product_package')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    public function __toString()
    {
        return $this->getName() . ' ' . $this->getCa() . ' EUR';
    }

    public function getCa(): ?string
    {
        return $this->ca;
    }

    public function setCa(?string $ca): self
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
}
