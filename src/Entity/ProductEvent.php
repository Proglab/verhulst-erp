<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProductEventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductEventRepository::class)]
class ProductEvent extends Product
{
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    public function __toString()
    {
        return $this->getName() . ' - (' . $this->getDate()->format('d/m/Y') . ')';
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }
}
