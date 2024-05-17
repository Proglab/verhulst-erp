<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\FastSalesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FastSalesRepository::class)]
class FastSales extends BaseSales
{
    #[ORM\Column]
    private bool $validate = false;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\Length(max: 11)]
    #[Assert\PositiveOrZero]
    protected ?string $forecast_price = null;


    public function isValidate(): ?bool
    {
        return $this->validate;
    }

    public function setValidate(bool $validate): static
    {
        $this->validate = $validate;

        return $this;
    }

    public function getForecastPrice(): ?float
    {
        return (float) $this->forecast_price;
    }

    public function setForecastPrice(string|float|null $forecastPrice): self
    {
        $this->forecast_price = (string) str_replace(',', '.', (string) $forecastPrice);

        return $this;
    }

}
