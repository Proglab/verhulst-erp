<?php

declare(strict_types=1);

namespace App\Entity\Budget;

use App\Entity\Trait\PrimaryKeyTrait;
use App\Repository\Budget\BudgetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'budget_budget')]
#[ORM\Entity(repositoryClass: BudgetRepository::class)]
class Budget
{
    use PrimaryKeyTrait;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'budgets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\OneToMany(mappedBy: 'budget', targetEntity: Category::class, cascade: ['persist', 'remove'])]
    private Collection $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
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

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->setBudget($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        if ($this->categories->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getBudget() === $this) {
                $category->setBudget(null);
            }
        }

        return $this;
    }

    public function getTotalPrice(): float
    {
        $price = 0.0;
        foreach ($this->getCategories() as $category) {
            $price += $category->getTotalPrice();
        }

        return $price;
    }

    public function getTotalPriceVat(): float
    {
        $price = 0.0;
        foreach ($this->getCategories() as $category) {
            $price += $category->getTotalPriceVat();
        }

        return $price;
    }

    public function getPercent(): float
    {
        return $this->event->getPercent();
    }

    public function getFee(): float
    {
        return $this->event->getPercent() * $this->getTotalPrice() / 100;
    }

    public function getFeeVat(): float
    {
        return $this->getFee() * 21 / 100;
    }

    public function getTotalFeeVat(): float
    {
        return $this->getFee() + $this->getFeeVat();
    }
}
