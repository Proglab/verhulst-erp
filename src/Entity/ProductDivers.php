<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProductDiversRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductDiversRepository::class)]
class ProductDivers extends Product
{
    #[ORM\ManyToOne(cascade:["persist"], inversedBy: 'product_divers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    public function __toString()
    {
        return $this->getName();
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
