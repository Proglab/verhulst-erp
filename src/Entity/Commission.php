<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CommissionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommissionRepository::class)]
#[UniqueEntity(fields: ['product', 'user'])]
class Commission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    #[Assert\Length(max: 6)]
    #[Assert\PositiveOrZero()]
    private ?string $percent_com = null;

    #[ORM\ManyToOne(inversedBy: 'commissions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\ManyToOne(inversedBy: 'commissions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPercentCom(): ?float
    {
        return (float) $this->percent_com;
    }

    public function setPercentCom(float|string|null $percent_com): self
    {
        $this->percent_com = (string) str_replace(',', '.', (string) $percent_com);

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
