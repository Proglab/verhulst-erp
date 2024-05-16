<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CompanyContactRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CompanyContactRepository::class)]
#[UniqueEntity('email')]
class CompanyContact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $firstname = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $lastname = null;

    #[ORM\Column(length: 2)]
    #[Assert\NotBlank]
    #[Assert\Language]
    #[Assert\Length(max: 2)]
    private ?string $lang = 'fr';

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(length: 30, nullable: true)]
    #[Assert\Length(max: 30)]
    private ?string $phone = null;

    #[ORM\Column(length: 30, nullable: true)]
    #[Assert\Length(max: 30)]
    private ?string $gsm = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $street = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Length(max: 20)]
    private ?string $pc = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $city = null;

    #[ORM\Column(length: 2, nullable: true)]
    #[Assert\Country()]
    private ?string $country = 'BE';

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $fonction = null;

    #[ORM\Column(nullable: true)]
    private ?array $interests = [];

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $note = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'contact')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Company $company = null;

    #[ORM\ManyToOne(inversedBy: 'companyContacts')]
    private ?User $added_by = null;

    #[ORM\OneToMany(mappedBy: 'contact', targetEntity: Sales::class)]
    private Collection $sales;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Todo::class, orphanRemoval: true)]
    private Collection $todos;

    #[ORM\Column(length: 1, nullable: true)]
    private ?string $sex = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $greeting = null;

    #[ORM\OneToMany(mappedBy: 'company_contact', targetEntity: CompanyContactNote::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $notes;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updated_dt = null;

    #[ORM\Column]
    private ?bool $mailing = true;

    /**
     * @var Collection<int, FastSales>
     */
    #[ORM\OneToMany(mappedBy: 'client', targetEntity: FastSales::class, orphanRemoval: true)]
    private Collection $fastSales;

    public function __construct()
    {
        $this->sales = new ArrayCollection();
        $this->todos = new ArrayCollection();
        $this->notes = new ArrayCollection();
        $this->fastSales = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getFirstname() . ' ' . $this->getLastname() . ' (' . $this->lang . ')';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFullName(): string
    {
        return $this->getFirstname() . ' ' . $this->getLastname();
    }

    public function getLang(): ?string
    {
        return $this->lang;
    }

    public function setLang(string $lang): self
    {
        $this->lang = $lang;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAddedBy(): ?User
    {
        return $this->added_by;
    }

    public function setAddedBy(?User $added_by): self
    {
        $this->added_by = $added_by;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getGsm(): ?string
    {
        return $this->gsm;
    }

    public function setGsm(?string $gsm): self
    {
        $this->gsm = $gsm;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): void
    {
        $this->street = $street;
    }

    public function getPc(): ?string
    {
        return $this->pc;
    }

    public function setPc(?string $pc): void
    {
        $this->pc = $pc;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    public function getFunction(): ?string
    {
        return $this->fonction;
    }

    public function setFunction(?string $function): void
    {
        $this->fonction = $function;
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
            $sale->setContact($this);
        }

        return $this;
    }

    public function removeSale(Sales $sale): self
    {
        if ($this->sales->removeElement($sale)) {
            // set the owning side to null (unless already changed)
            if ($sale->getContact() === $this) {
                $sale->setContact(null);
            }
        }

        return $this;
    }

    public function getInterests(): array
    {
        return $this->interests;
    }

    public function setInterests(?array $interests): self
    {
        $this->interests = $interests;

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
            $todo->setClient($this);
        }

        return $this;
    }

    public function removeTodo(Todo $todo): self
    {
        if ($this->todos->removeElement($todo)) {
            // set the owning side to null (unless already changed)
            if ($todo->getClient() === $this) {
                $todo->setClient(null);
            }
        }

        return $this;
    }

    public function getSex(): ?string
    {
        return $this->sex;
    }

    public function setSex(?string $sex): static
    {
        $this->sex = $sex;

        return $this;
    }

    public function getGreeting(): ?string
    {
        return $this->greeting;
    }

    public function setGreeting(?string $greeting): static
    {
        $this->greeting = $greeting;

        return $this;
    }

    /**
     * @return Collection<int, CompanyContactNote>
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(CompanyContactNote $note): static
    {
        if (!$this->notes->contains($note)) {
            $this->notes->add($note);
            $note->setCompanyContact($this);
        }

        return $this;
    }

    public function removeNote(CompanyContactNote $note): static
    {
        if ($this->notes->removeElement($note)) {
            // set the owning side to null (unless already changed)
            if ($note->getCompanyContact() === $this) {
                $note->setCompanyContact(null);
            }
        }

        return $this;
    }

    public function getUpdatedDt(): ?\DateTimeInterface
    {
        return $this->updated_dt;
    }

    public function setUpdatedDt(?\DateTimeInterface $updated_dt): static
    {
        $this->updated_dt = $updated_dt;

        return $this;
    }

    public function isMailing(): ?bool
    {
        return $this->mailing;
    }

    public function setMailing(bool $mailing): static
    {
        $this->mailing = $mailing;

        return $this;
    }

    /**
     * @return Collection<int, FastSales>
     */
    public function getFastSales(): Collection
    {
        return $this->fastSales;
    }

    public function addFastSale(FastSales $fastSale): static
    {
        if (!$this->fastSales->contains($fastSale)) {
            $this->fastSales->add($fastSale);
            $fastSale->setClient($this);
        }

        return $this;
    }

    public function removeFastSale(FastSales $fastSale): static
    {
        if ($this->fastSales->removeElement($fastSale)) {
            // set the owning side to null (unless already changed)
            if ($fastSale->getClient() === $this) {
                $fastSale->setClient(null);
            }
        }

        return $this;
    }
}
