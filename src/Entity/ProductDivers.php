<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProductDiversRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductDiversRepository::class)]
class ProductDivers extends Product
{
    public function __toString()
    {
        return $this->getProject()->getName() . ' - ' . $this->getName();
    }
}
