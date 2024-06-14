<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\FastSalesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FastSalesRepository::class)]
class FastSales extends BaseSales
{
    #[ORM\Column]
    private bool $validate = false;

    public function isValidate(): ?bool
    {
        return $this->validate;
    }

    public function setValidate(bool $validate): static
    {
        $this->validate = $validate;

        return $this;
    }
}
