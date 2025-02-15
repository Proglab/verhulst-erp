<?php

declare(strict_types=1);

namespace App\Entity\Budget\Ref;

use App\Entity\Trait\PrimaryKeyTrait;
use App\Repository\Budget\Ref\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'budget_category_reference')]
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    use PrimaryKeyTrait;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: SubCategory::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $sub_categories;

    public function __construct()
    {
        $this->sub_categories = new ArrayCollection();
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
}
