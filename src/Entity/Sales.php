<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SalesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SalesRepository::class)]
class Sales
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?int $price = null;

    #[ORM\ManyToOne(inversedBy: 'sales')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToMany(targetEntity: CompanyContact::class, inversedBy: 'sales')]
    private Collection $contact;

    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 4, nullable: true)]
    private ?float $percent_vr = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 4, nullable: true)]
    private ?float $percent_com = null;

    #[ORM\ManyToOne(inversedBy: 'sales')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    public function __construct()
    {
        $this->contact = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrice(): ?int
    {
        return (int) $this->price;
    }

    public function setPrice(?int $price): self
    {
        $this->price = $price;

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

    /**
     * @return Collection<int, CompanyContact>
     */
    public function getContact(): Collection
    {
        return $this->contact;
    }

    public function addContact(CompanyContact $contact): self
    {
        if (!$this->contact->contains($contact)) {
            $this->contact->add($contact);
        }

        return $this;
    }

    public function removeContact(CompanyContact $contact): self
    {
        $this->contact->removeElement($contact);

        return $this;
    }

    public function getPercentVr(): ?float
    {
        return $this->percent_vr;
    }

    public function getEuroVr(): float
    {
        return $this->getMarge() * $this->getPercentVr();
    }

    public function getEuroCom(): float
    {
        return $this->getMarge() * $this->getPercentCom();
    }

    public function getMarge(): float
    {
        return $this->getPrice() / 100 - $this->product->getPa() / 100;
    }

    public function getDiffCa(): float
    {
        return $this->getMarge() - $this->getEuroCom() - $this->getEuroVr();
    }

    public function getPa(): float
    {
        return $this->product->getPa();
    }

    public function setPercentVr(?float $percent_vr): self
    {
        $this->percent_vr = $percent_vr;

        return $this;
    }

    public function getPercentCom(): ?float
    {
        return $this->percent_com;
    }

    public function setPercentCom(?float $percent_com): self
    {
        $this->percent_com = $percent_com;

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }
}
