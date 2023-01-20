<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProductSponsoringRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductSponsoringRepository::class)]
class ProductSponsoring extends Product
{
    public function __toString()
    {
        return $this->getName();
    }

}
