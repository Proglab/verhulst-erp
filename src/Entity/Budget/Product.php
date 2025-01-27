<?php

declare(strict_types=1);

namespace App\Entity\Budget;

use App\Repository\Budget\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'budget_product')]
#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $quantity = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $price = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SubCategory $sub_category = null;

    #[ORM\ManyToOne(inversedBy: 'product')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Vat $vat = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Supplier $supplier = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $real_price = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $filename = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $offer_price = null;

    #[ORM\ManyToMany(targetEntity: Invoice::class, mappedBy: 'products')]
    private Collection $invoices;

    public function __construct()
    {
        $this->invoices = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getSubCategory()->getCategory()->getBudget()->getName() . ' - ' . $this->getSubCategory()->getCategory()->getName() . ' - ' . $this->getSubCategory()->getName() . ' - ' . $this->getTitle();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;
        
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return (int) $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = (string) $quantity;

        return $this;
    }

    public function getPrice(): ?float
    {
        return (float) $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = (string) $price;

        return $this;
    }

    public function getSubCategory(): ?SubCategory
    {
        return $this->sub_category;
    }

    public function setSubCategory(?SubCategory $sub_category): static
    {
        $this->sub_category = $sub_category;

        return $this;
    }

    public function getTotalPrice(): ?float
    {
        return $this->getQuantity() * $this->getPrice();
    }

    public function getVat(): ?Vat
    {
        return $this->vat;
    }

    public function setVat(?Vat $vat): static
    {
        $this->vat = $vat;

        return $this;
    }

    public function getVatPrice(): float
    {
        return $this->getTotalPrice() * $this->getVat()->getPercent() / 100;
    }

    public function getTotalPriceVat(): float
    {
        return $this->getTotalPrice() + $this->getVatPrice();
    }

    public function getSupplier(): ?Supplier
    {
        return $this->supplier;
    }

    public function setSupplier(?Supplier $supplier): static
    {
        $this->supplier = $supplier;

        return $this;
    }

    public function getRealPrice(): ?float
    {
        return (float) $this->real_price;
    }

    public function setRealPrice(?float $real_price): static
    {
        $this->real_price = (string) $real_price;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getRealPriceVat(): ?float
    {
        return $this->getRealPrice() + $this->getVatRealPrice();
    }

    public function getVatRealPrice(): float
    {
        return $this->getRealPrice() * $this->getVat()->getPercent() / 100;
    }

    public function getTotalRealPrice(): ?float
    {
        return $this->getRealPrice() * $this->getQuantity();
    }

    public function getTotalRealPriceVat(): ?float
    {
        return $this->getRealPriceVat() * $this->getQuantity();
    }

    public function getVatTotalRealPrice(): float
    {
        return $this->getTotalRealPriceVat() - $this->getTotalRealPrice();
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getOfferPrice(): ?string
    {
        return $this->offer_price;
    }

    public function setOfferPrice(?string $offer_price): static
    {
        $this->offer_price = $offer_price;

        return $this;
    }

    /**
     * @return Collection<int, Invoice>
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function addInvoice(Invoice $invoice): static
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices->add($invoice);
            $invoice->addProduct($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): static
    {
        if ($this->invoices->removeElement($invoice)) {
            $invoice->removeProduct($this);
        }

        return $this;
    }
}
