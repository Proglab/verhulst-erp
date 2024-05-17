<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\BaseSalesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BaseSalesRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap(['normal' => Sales::class, 'fast' => FastSales::class])]
#[ORM\Table(name: 'sales')]
class BaseSales
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $po = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\Length(max: 11)]
    protected ?string $price = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 11)]
    #[Assert\PositiveOrZero]
    protected ?string $pa = '0';

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: false)]
    #[Assert\Length(max: 6)]
    #[Assert\PositiveOrZero]
    protected ?string $percent_vr = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 4, scale: 2, nullable: false)]
    #[Assert\Length(max: 5)]
    #[Assert\PositiveOrZero]
    protected ?string $percent_com = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    protected ?\DateTimeInterface $date = null;

    #[ORM\Column(nullable: false)]
    #[Assert\Positive]
    protected int $quantity = 1;

    #[ORM\ManyToOne(inversedBy: 'sales')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    protected ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'sales')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    protected ?CompanyContact $contact = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string|null $name): self
    {
        $this->name = $name;

        return $this;
    }
    public function getPo(): ?string
    {
        return $this->po;
    }

    public function setPo(string|null $po): self
    {
        $this->po = $po;

        return $this;
    }

    public function getPrice(): ?float
    {
        return (float) $this->price;
    }

    public function setPrice(string|float|null $price): self
    {
        $this->price = (string) str_replace(',', '.', (string) $price);

        return $this;
    }

    public function getPa(): ?float
    {
        return (float) $this->pa;
    }

    public function setPa(string|float|null $pa): self
    {
        $this->pa = (string) str_replace(',', '.', (string) $pa);

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

    public function getPercentVr(): ?float
    {
        return (float) $this->percent_vr;
    }

    public function setPercentVr(?float $percent_vr): self
    {
        $this->percent_vr = (string) $percent_vr;

        return $this;
    }

    public function getPercentCom(): ?float
    {
        return (float) $this->percent_com;
    }

    public function setPercentCom(?float $percent_com): self
    {
        $this->percent_com = (string) $percent_com;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }


    public function getContact(): ?CompanyContact
    {
        return $this->contact;
    }

    public function setContact(?CompanyContact $contact): self
    {
        $this->contact = $contact;

        return $this;
    }



}