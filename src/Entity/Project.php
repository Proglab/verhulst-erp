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
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $name = null;

    private ?bool $mail = null;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: ProductEvent::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $product_event;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: ProductPackageVip::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $product_package;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: ProductSponsoring::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $product_sponsoring;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: ProductDivers::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $product_divers;

    #[ORM\Column]
    private ?bool $archive = false;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_begin = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_end = null;

    public function __construct()
    {
        $this->product_event = new ArrayCollection();
        $this->product_package = new ArrayCollection();
        $this->product_sponsoring = new ArrayCollection();
        $this->product_divers = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $this->name = $this->name . ' (clone)';
            $this->product_divers = new ArrayCollection();
            $this->product_event = new ArrayCollection();
            $this->product_package = new ArrayCollection();
            $this->product_sponsoring = new ArrayCollection();
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

    /**
     * @return Collection<int, ProductEvent>
     */
    public function getProductEvent(): Collection
    {
        return $this->product_event;
    }

    public function addProductEvent(ProductEvent $productEvent): self
    {
        if (!$this->product_event->contains($productEvent)) {
            $this->product_event->add($productEvent);
            $productEvent->setProject($this);
        }

        return $this;
    }

    public function removeProductEvent(ProductEvent $productEvent): self
    {
        if ($this->product_event->removeElement($productEvent)) {
            // set the owning side to null (unless already changed)
            if ($productEvent->getProject() === $this) {
                $productEvent->setProject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductPackageVip>
     */
    public function getProductPackage(): Collection
    {
        return $this->product_package;
    }

    public function addProductPackage(ProductPackageVip $productPackage): self
    {
        if (!$this->product_package->contains($productPackage)) {
            $this->product_package->add($productPackage);
            $productPackage->setProject($this);
        }

        return $this;
    }

    public function removeProductPackage(ProductPackageVip $productPackage): self
    {
        if ($this->product_package->removeElement($productPackage)) {
            // set the owning side to null (unless already changed)
            if ($productPackage->getProject() === $this) {
                $productPackage->setProject(null);
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

    public function addProductSponsoring(ProductSponsoring $productSponsoring): self
    {
        if (!$this->product_sponsoring->contains($productSponsoring)) {
            $this->product_sponsoring->add($productSponsoring);
            $productSponsoring->setProject($this);
        }

        return $this;
    }

    public function removeProductSponsoring(ProductSponsoring $productSponsoring): self
    {
        if ($this->product_sponsoring->removeElement($productSponsoring)) {
            // set the owning side to null (unless already changed)
            if ($productSponsoring->getProject() === $this) {
                $productSponsoring->setProject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductDivers>
     */
    public function getProductDivers(): Collection
    {
        return $this->product_divers;
    }

    public function addProductDiver(ProductDivers $productDiver): self
    {
        if (!$this->product_divers->contains($productDiver)) {
            $this->product_divers->add($productDiver);
            $productDiver->setProject($this);
        }

        return $this;
    }

    public function removeProductDiver(ProductDivers $productDiver): self
    {
        if ($this->product_divers->removeElement($productDiver)) {
            // set the owning side to null (unless already changed)
            if ($productDiver->getProject() === $this) {
                $productDiver->setProject(null);
            }
        }

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
}
