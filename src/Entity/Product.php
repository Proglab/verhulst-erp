<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap(['package' => ProductPackageVip::class, 'event' => ProductEvent::class, 'sponsor' => ProductSponsoring::class, 'divers' => ProductDivers::class])]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    #[Assert\Length(max: 6)]
    #[Assert\PositiveOrZero]
    private ?float $percent_vr = 0.0;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $doc = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    #[Assert\Length(max: 6)]
    #[Assert\PositiveOrZero]
    private ?float $percent_freelance = 0.0;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    #[Assert\Length(max: 6)]
    #[Assert\PositiveOrZero]
    private ?float $percent_salarie = 0.0;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    #[Assert\Length(max: 6)]
    #[Assert\PositiveOrZero]
    private ?float $percent_tv = 0.0;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Commission::class, orphanRemoval: true)]
    private Collection $commissions;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Sales::class, orphanRemoval: true)]
    private Collection $sales;

    public function __construct()
    {
        $this->sales = new ArrayCollection();
        $this->commissions = new ArrayCollection();
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $this->commissions = new ArrayCollection();
            $this->sales = new ArrayCollection();
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

    public function getPercentVr(): ?float
    {
        return $this->percent_vr;
    }

    public function setPercentVr(float|string|null $percent_vr): self
    {
        $this->percent_vr = (float) str_replace(',', '.', $percent_vr);

        return $this;
    }

    public function getPercentFreelance(): ?float
    {
        return $this->percent_freelance;
    }

    public function setPercentFreelance(string $percent_freelance): self
    {
        $this->percent_freelance = (float) str_replace(',', '.', $percent_freelance);

        return $this;
    }

    public function getPercentSalarie(): ?float
    {
        return $this->percent_salarie;
    }

    public function setPercentSalarie(string $percent_salarie): self
    {
        $this->percent_salarie = (float) str_replace(',', '.', $percent_salarie);

        return $this;
    }

    public function getPercentTv(): ?float
    {
        return $this->percent_tv;
    }

    public function setPercentTv(string $percent_tv): self
    {
        $this->percent_tv = (float) str_replace(',', '.', $percent_tv);

        return $this;
    }

    /**
     * @return Collection<int, Commission>
     */
    public function getCommissions(): Collection
    {
        return $this->commissions;
    }

    public function addCommission(Commission $commission): self
    {
        if (!$this->commissions->contains($commission)) {
            $this->commissions->add($commission);
            $commission->setProduct($this);
        }

        return $this;
    }

    public function removeCommission(Commission $commission): self
    {
        if ($this->commissions->removeElement($commission)) {
            // set the owning side to null (unless already changed)
            if ($commission->getProduct() === $this) {
                $commission->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Sales>
     */
    public function getSales(): Collection
    {
        return $this->sales;
    }

    public function addSale(Sales $sale): self
    {
        if (!$this->sales->contains($sale)) {
            $this->sales->add($sale);
            $sale->setProduct($this);
        }

        return $this;
    }

    public function removeSale(Sales $sale): self
    {
        if ($this->sales->removeElement($sale)) {
            // set the owning side to null (unless already changed)
            if ($sale->getProduct() === $this) {
                $sale->setProduct(null);
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
