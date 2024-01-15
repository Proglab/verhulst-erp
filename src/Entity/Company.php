<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
#[UniqueEntity('vat_number')]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $name = null;

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

    #[ORM\Column(length: 30, nullable: true)]
    #[Assert\Length(max: 30)]
    #[Assert\Regex('/^((AT)?U[0-9]{8}|(BE)?0[0-9]{9}|(BG)?[0-9]{9,10}|(CY)?[0-9]{8}L|(CZ)?[0-9]{8,10}|(DE)?[0-9]{9}|(DK)?[0-9]{8}|(EE)?[0-9]{9}|(EL|GR)?[0-9]{9}|(ES)?[0-9A-Z][0-9]{7}[0-9A-Z]|(FI)?[0-9]{8}|(FR)?[0-9A-Z]{2}[0-9]{9}|(GB)?([0-9]{9}([0-9]{3})?|[A-Z]{2}[0-9]{3})|(HU)?[0-9]{8}|(IE)?[0-9]S[0-9]{5}L|(IT)?[0-9]{11}|(LT)?([0-9]{9}|[0-9]{12})|(LU)?[0-9]{8}|(LV)?[0-9]{11}|(MT)?[0-9]{8}|(NL)?[0-9]{9}B[0-9]{2}|(PL)?[0-9]{10}|(PT)?[0-9]{9}|(RO)?[0-9]{2,10}|(SE)?[0-9]{12}|(SI)?[0-9]{8}|(SK)?[0-9]{10})$/', 'Mettre au format Européen (BE0760577097)')]
    private ?string $vat_number = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $note = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $billing_street = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Length(max: 20)]
    private ?string $billing_pc = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $billing_city = null;

    #[ORM\Column(length: 2, nullable: true)]
    #[Assert\Country()]
    #[Assert\Length(max: 2)]
    private ?string $billing_country = 'BE';

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Email(message: 'Veuillez renseigner un email valide')]
    #[Assert\Length(max: 255)]
    private ?string $billing_mail = null;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: CompanyContact::class, cascade: ['persist'], orphanRemoval: true)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private Collection $contact;

    public function getTodos()
    {
        $return = [];
        /** @var CompanyContact $contact */
        foreach ($this->contact as $contact) {
            foreach ($contact->getTodos() as $todo) {
                $return[$todo->getDateReminder()->format('Ymd')] = $todo;
            }
        }
        krsort($return);
        return $return;
    }

    public function getNotes()
    {
        $return = [];
        /** @var CompanyContact $contact */
        foreach ($this->contact as $contact) {
            foreach ($contact->getNotes() as $note) {
                $return[$note->getCreatedDt()->format('Ymd')] = $note;
            }
        }
        krsort($return);
        return $return;
    }
    #[ORM\Column]
    private bool $vat_na = false;

    public function __construct()
    {
        $this->contact = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName();
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

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getPc(): ?string
    {
        return $this->pc;
    }

    public function setPc(?string $pc): self
    {
        $this->pc = $pc;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getVatNumber(): ?string
    {
        return $this->vat_number;
    }

    public function setVatNumber(?string $vat_number): self
    {
        $this->vat_number = $vat_number;

        return $this;
    }

    /**
     * @return Collection<int, CompanyContact>
     */
    public function getContact(): Collection
    {
        return $this->contact;
    }

    public function addContact(CompanyContact $contact): self
    {
        if (!$this->contact->contains($contact)) {
            $this->contact->add($contact);
            $contact->setCompany($this);
        }

        return $this;
    }

    public function removeContact(CompanyContact $contact): self
    {
        if ($this->contact->removeElement($contact)) {
            // set the owning side to null (unless already changed)
            if ($contact->getCompany() === $this) {
                $contact->setCompany(null);
            }
        }

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

    public function getBillingStreet(): ?string
    {
        return $this->billing_street;
    }

    public function setBillingStreet(?string $billing_street): self
    {
        $this->billing_street = $billing_street;

        return $this;
    }

    public function getBillingPc(): ?string
    {
        return $this->billing_pc;
    }

    public function setBillingPc(?string $billing_pc): self
    {
        $this->billing_pc = $billing_pc;

        return $this;
    }

    public function getBillingCity(): ?string
    {
        return $this->billing_city;
    }

    public function setBillingCity(?string $billing_city): self
    {
        $this->billing_city = $billing_city;

        return $this;
    }

    public function getBillingCountry(): ?string
    {
        return $this->billing_country;
    }

    public function setBillingCountry(?string $billing_country): self
    {
        $this->billing_country = $billing_country;

        return $this;
    }

    public function getBillingMail(): ?string
    {
        return $this->billing_mail;
    }

    public function setBillingMail(?string $billing_mail): self
    {
        $this->billing_mail = $billing_mail;

        return $this;
    }

    public function isVatNa(): ?bool
    {
        return $this->vat_na;
    }

    public function setVatNa(bool $vat_na): self
    {
        $this->vat_na = $vat_na;

        return $this;
    }
}
