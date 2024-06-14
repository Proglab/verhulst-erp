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
#[ORM\DiscriminatorMap(['package' => ProductPackageVip::class, 'sponsor' => ProductSponsoring::class])]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    protected ?string $name = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 17, scale: 14, nullable: true)]
    #[Assert\PositiveOrZero]
    protected ?string $percent_vr = '0';

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    protected ?string $doc = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    #[Assert\Length(max: 6)]
    #[Assert\PositiveOrZero]
    protected ?string $percent_freelance = '10';

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    #[Assert\Length(max: 6)]
    #[Assert\PositiveOrZero]
    protected ?string $percent_salarie = '5';

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    #[Assert\Length(max: 6)]
    #[Assert\PositiveOrZero]
    protected ?string $percent_tv = '3';

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?\DateTimeInterface $date_begin = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?\DateTimeInterface $date_end = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Commission::class, orphanRemoval: true)]
    protected Collection $commissions;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: BaseSales::class, orphanRemoval: true)]
    protected Collection $sales;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\Length(max: 11)]
    #[Assert\PositiveOrZero]
    protected ?string $ca = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\Length(max: 11)]
    #[Assert\PositiveOrZero]
    protected ?string $pa = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    protected ?Project $project = null;


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
        return (float) $this->percent_vr;
    }

    public function setPercentVr(float|string|null $percent_vr): self
    {
        $this->percent_vr = str_replace(',', '.', (string) $percent_vr);

        return $this;
    }

    public function getPercentFreelance(): ?float
    {
        return (float) $this->percent_freelance;
    }

    public function setPercentFreelance(string $percent_freelance): self
    {
        $this->percent_freelance = str_replace(',', '.', $percent_freelance);

        return $this;
    }

    public function getPercentSalarie(): ?float
    {
        return (float) $this->percent_salarie;
    }

    public function setPercentSalarie(string $percent_salarie): self
    {
        $this->percent_salarie = str_replace(',', '.', $percent_salarie);

        return $this;
    }

    public function getPercentTv(): ?float
    {
        return (float) $this->percent_tv;
    }

    public function setPercentTv(string $percent_tv): self
    {
        $this->percent_tv = str_replace(',', '.', $percent_tv);

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

    public function getQuantitySales(): int
    {
        $quantity = 0;
        foreach ($this->getSales() as $sale) {
            $quantity += $sale->getQuantity();
        }

        return $quantity;
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

    public function getDateBegin(): ?\DateTimeInterface
    {
        return $this->date_begin;
    }

    public function setDateBegin(?\DateTimeInterface $date): self
    {
        $this->date_begin = $date;

        return $this;
    }

    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->date_end;
    }

    public function setDateEnd(?\DateTimeInterface $date): self
    {
        $this->date_end = $date;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->getDateBegin();
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->setDateBegin($date);

        return $this;
    }

    public function getCa(): ?float
    {
        return (float) $this->ca;
    }

    public function setCa(?float $ca): self
    {
        $this->ca = (string) $ca;

        return $this;
    }

    public function getPa(): ?float
    {
        return (float) $this->pa;
    }

    public function setPa(?float $pa): self
    {
        $this->pa = (string) $pa;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;

        return $this;
    }
}
