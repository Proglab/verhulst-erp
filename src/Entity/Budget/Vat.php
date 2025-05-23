<?php

declare(strict_types=1);

namespace App\Entity\Budget;

use App\Entity\Trait\PrimaryKeyTrait;
use App\Repository\Budget\VatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'budget_vat')]
#[ORM\Entity(repositoryClass: VatRepository::class)]
class Vat
{
    use PrimaryKeyTrait;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private ?string $percent = null;

    #[ORM\OneToMany(mappedBy: 'vat', targetEntity: Product::class, orphanRemoval: true)]
    private Collection $product;

    public function __construct()
    {
        $this->product = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->percent . '%';
    }

    public function getPercent(): ?float
    {
        return (float) $this->percent;
    }

    public function setPercent(float $percent): static
    {
        $this->percent = (string) $percent;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProduct(): Collection
    {
        return $this->product;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->product->contains($product)) {
            $this->product->add($product);
            $product->setVat($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->product->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getVat() === $this) {
                $product->setVat(null);
            }
        }

        return $this;
    }
}
