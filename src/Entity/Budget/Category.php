<?php

declare(strict_types=1);

namespace App\Entity\Budget;

use App\Repository\Budget\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'budget_category')]
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: SubCategory::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $sub_categories;

    #[ORM\ManyToOne(inversedBy: 'categories')]
    private ?Budget $budget = null;

    public function __construct()
    {
        $this->sub_categories = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, SubCategory>
     */
    public function getSubCategories(): Collection
    {
        return $this->sub_categories;
    }

    public function addSubCategory(SubCategory $subCategory): static
    {
        if (!$this->sub_categories->contains($subCategory)) {
            $this->sub_categories->add($subCategory);
            $subCategory->setCategory($this);
        }

        return $this;
    }

    public function removeSubCategory(SubCategory $subCategory): static
    {
        if ($this->sub_categories->removeElement($subCategory)) {
            // set the owning side to null (unless already changed)
            if ($subCategory->getCategory() === $this) {
                $subCategory->setCategory(null);
            }
        }

        return $this;
    }

    public function getBudget(): ?Budget
    {
        return $this->budget;
    }

    public function setBudget(?Budget $budget): static
    {
        $this->budget = $budget;

        return $this;
    }

    public function getTotalPrice(): float
    {
        $price = 0.0;
        foreach ($this->getSubCategories() as $subCategory) {
            $price += $subCategory->getTotalPrice();
        }

        return $price;
    }
}
