<?php

namespace App\Entity\Budget;

use App\Entity\User;
use App\Repository\Budget\InvoiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $doc = null;

    #[ORM\Column]
    private ?bool $validated = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $validated_date = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $validated_user = null;

    #[ORM\ManyToMany(targetEntity: Product::class, inversedBy: 'invoices')]
    private Collection $products;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    private ?Supplier $supplier = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    private ?Event $event = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $price = null;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDoc(): ?string
    {
        return $this->doc;
    }

    public function setDoc(?string $doc): static
    {
        $this->doc = $doc;

        return $this;
    }

    public function isValidated(): ?bool
    {
        return $this->validated;
    }

    public function setValidated(bool $validated): static
    {
        $this->validated = $validated;

        return $this;
    }

    public function getValidatedDate(): ?\DateTimeInterface
    {
        return $this->validated_date;
    }

    public function setValidatedDate(?\DateTimeInterface $validated_date): static
    {
        $this->validated_date = $validated_date;

        return $this;
    }

    public function getValidatedUser(): ?User
    {
        return $this->validated_user;
    }

    public function setValidatedUser(?User $validated_user): static
    {
        $this->validated_user = $validated_user;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        $this->products->removeElement($product);

        return $this;
    }

    public function resetProduct()
    {
        $this->products = new ArrayCollection();
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

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): static
    {
        $this->price = $price;

        return $this;
    }
}
