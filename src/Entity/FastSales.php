<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\FastSalesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FastSalesRepository::class)]
class FastSales extends BaseSales
{
}
