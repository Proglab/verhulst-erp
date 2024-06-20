<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project
{
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    protected ?string $doc = null;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\OneToMany(mappedBy: 'project', targetEntity: Product::class, cascade: ['remove'])]
    #[ORM\OrderBy(['date_begin' => 'ASC'])]
    protected Collection $products;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $name = null;

    private ?bool $mail = null;
    #[ORM\Column]
    private ?bool $archive = false;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_begin = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_end = null;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: Todo::class, cascade: ['remove'])]
    private Collection $todos;

    public function __construct()
    {
        $this->todos = new ArrayCollection();
        $this->products = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $this->name .= ' (clone)';
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function isMail(): ?bool
    {
        return $this->mail;
    }

    public function setMail(?bool $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function isArchive(): ?bool
    {
        return $this->archive;
    }

    public function setArchive(bool $archive): self
    {
        $this->archive = $archive;

        return $this;
    }

    public function getDateBegin(): ?\DateTimeInterface
    {
        return $this->date_begin;
    }

    public function setDateBegin(?\DateTimeInterface $date_begin): self
    {
        $this->date_begin = $date_begin;

        return $this;
    }

    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->date_end;
    }

    public function setDateEnd(?\DateTimeInterface $date_end): self
    {
        $this->date_end = $date_end;

        return $this;
    }

    /**
     * @return Collection<int, Todo>
     */
    public function getTodos(): Collection
    {
        return $this->todos;
    }

    public function addTodo(Todo $todo): self
    {
        if (!$this->todos->contains($todo)) {
            $this->todos->add($todo);
            $todo->setProject($this);
        }

        return $this;
    }

    public function removeTodo(Todo $todo): self
    {
        if ($this->todos->removeElement($todo)) {
            // set the owning side to null (unless already changed)
            if ($todo->getProject() === $this) {
                $todo->setProject(null);
            }
        }

        return $this;
    }

    public function getDoc(): ?string
    {
        return $this->doc;
    }

    public function setDoc(?string $doc): self
    {
        $this->doc = $doc;

        return $this;
    }

    public function getUrl(): string
    {
        return __DIR__ . '/../../../../shared/public/files/products/' . $this->getDoc();
    }

    public function getDownloadUrl(): string
    {
        if (null !== $this->getDoc()) {
            return '<a href="/files/products/' . $this->getDoc() . '"><i class="fa-regular fa-file"></i> Télécharger</a>';
        }

        return '';
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
            $product->setProject($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getProject() === $this) {
                $product->setProject(null);
            }
        }

        return $this;
    }

    public function getProductSponsoring(): array
    {
        $return = [];
        foreach ($this->getProducts() as $product) {
            if ($product instanceof ProductSponsoring) {
                $return[] = $product;
            }
        }

        return $return;
    }

    public function getProductPackage(): array
    {
        $return = [];
        foreach ($this->getProducts() as $product) {
            if ($product instanceof ProductPackageVip) {
                $return[] = $product;
            }
        }

        return $return;
    }
}
