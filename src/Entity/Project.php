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

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: Todo::class)]
    private Collection $todos;

    /**
     * @var Collection<int, ProductPackageVip>
     */
    #[ORM\OneToMany(mappedBy: 'project', targetEntity: ProductPackageVip::class)]
    private Collection $product_package;

    /**
     * @var Collection<int, ProductSponsoring>
     */
    #[ORM\OneToMany(mappedBy: 'project', targetEntity: ProductSponsoring::class)]
    private Collection $product_sponsoring;

    public function __construct()
    {
        $this->todos = new ArrayCollection();
        $this->product_package = new ArrayCollection();
        $this->product_sponsoring = new ArrayCollection();
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
     * @return Collection<int, ProductPackageVip>
     */
    public function getProductPackage(): Collection
    {
        return $this->product_package;
    }

    public function addProductPackage(ProductPackageVip $productsPackage): static
    {
        if (!$this->product_package->contains($productsPackage)) {
            $this->product_package->add($productsPackage);
            $productsPackage->setProject($this);
        }

        return $this;
    }

    public function removeProductPackage(ProductPackageVip $productsPackage): static
    {
        if ($this->product_package->removeElement($productsPackage)) {
            // set the owning side to null (unless already changed)
            if ($productsPackage->getProject() === $this) {
                $productsPackage->setProject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductSponsoring>
     */
    public function getProductSponsoring(): Collection
    {
        return $this->product_sponsoring;
    }

    public function addProductSponsoring(ProductSponsoring $productSponsoring): static
    {
        if (!$this->product_sponsoring->contains($productSponsoring)) {
            $this->product_sponsoring->add($productSponsoring);
            $productSponsoring->setProject($this);
        }

        return $this;
    }

    public function removeProductSponsoring(ProductSponsoring $productSponsoring): static
    {
        if ($this->product_sponsoring->removeElement($productSponsoring)) {
            // set the owning side to null (unless already changed)
            if ($productSponsoring->getProject() === $this) {
                $productSponsoring->setProject(null);
            }
        }

        return $this;
    }
}
