<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\FastSalesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FastSalesRepository::class)]
class FastSales
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $po = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $forecast_budget = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $final_budget = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $com_sale = null;

    #[ORM\ManyToOne(inversedBy: 'fastSales')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'fastSales')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CompanyContact $client = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPo(): ?string
    {
        return $this->po;
    }

    public function setPo(string $po): static
    {
        $this->po = $po;

        return $this;
    }

    public function getForecastBudget(): ?string
    {
        return $this->forecast_budget;
    }

    public function setForecastBudget(?string $forecast_budget): static
    {
        $this->forecast_budget = $forecast_budget;

        return $this;
    }

    public function getFinalBudget(): ?string
    {
        return $this->final_budget;
    }

    public function setFinalBudget(?string $final_budget): static
    {
        $this->final_budget = $final_budget;

        return $this;
    }

    public function getComSale(): ?string
    {
        return $this->com_sale;
    }

    public function setComSale(string $com_sale): static
    {
        $this->com_sale = $com_sale;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getClient(): ?CompanyContact
    {
        return $this->client;
    }

    public function setClient(?CompanyContact $client): static
    {
        $this->client = $client;

        return $this;
    }
}
