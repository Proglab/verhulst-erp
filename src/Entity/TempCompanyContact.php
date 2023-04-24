<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TempCompanyContactRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TempCompanyContactRepository::class)]
class TempCompanyContact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $lastname = null;

    #[ORM\Column(length: 2)]
    #[Assert\Language]
    #[Assert\Length(max: 2)]
    private ?string $lang = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Assert\Email]
    #[Assert\NotBlank]
    private ?string $email = null;

    #[ORM\Column(length: 30, nullable: true)]
    #[Assert\Length(max: 30)]
    #[Assert\Regex('/^\+[1-9]\d{9,14}$/', 'Mettre le numéro sous format international (+32499163111)')]
    private ?string $phone = null;

    #[ORM\Column(length: 30, nullable: true)]
    #[Assert\Length(max: 30)]
    #[Assert\Regex('/^\+[1-9]\d{9,14}$/', 'Mettre le numéro sous format international (+32499163111)')]
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
    private ?string $country = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $fonction = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'contact')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TempCompany $company = null;

    #[ORM\ManyToOne(inversedBy: 'companyContacts')]
    private ?User $added_by = null;

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

    public function getCompany(): ?TempCompany
    {
        return $this->company;
    }

    public function setCompany(?TempCompany $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getPhone(): ?string
    {
        if (empty($this->phone)) {
            return null;
        }

        $phone = str_replace(' ', '', $this->phone);
        $phone = str_replace('/', '', $phone);
        $phone = str_replace('.', '', $phone);

        $this->phone = $phone;

        return $phone;
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

    public function getGsm(): ?string
    {
        if (empty($this->gsm)) {
            return null;
        }

        $phone = str_replace(' ', '', $this->gsm);
        $phone = str_replace('/', '', $phone);
        $phone = str_replace('.', '', $phone);

        $this->gsm = $phone;

        return $phone;
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
}
