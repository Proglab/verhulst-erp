<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProductEventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductEventRepository::class)]
class ProductEvent extends Product
{
    public function __toString()
    {
        return $this->getName();
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->getDateBegin();
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->setDateBegin($date);

        return $this;
    }
}
