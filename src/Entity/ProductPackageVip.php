<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProductPackageVipRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductPackageVipRepository::class)]
class ProductPackageVip extends Product
{
    #[ORM\ManyToOne(inversedBy: 'product_package')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    public function __toString()
    {
        return $this->getName() . ' ' . $this->getCa() . ' EUR';
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
