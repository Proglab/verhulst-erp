<?php

namespace App\Entity;

use App\Repository\CompanyContactNoteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompanyContactNoteRepository::class)]
class CompanyContactNote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $note = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_dt = null;

    #[ORM\ManyToOne(inversedBy: 'notes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CompanyContact $company_contact = null;

    #[ORM\ManyToOne(inversedBy: 'notes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(string $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getCreatedDt(): ?\DateTimeInterface
    {
        return $this->created_dt;
    }

    public function setCreatedDt(\DateTimeInterface $created_dt): static
    {
        $this->created_dt = $created_dt;

        return $this;
    }

    public function getCompanyContact(): ?CompanyContact
    {
        return $this->company_contact;
    }

    public function setCompanyContact(?CompanyContact $company_contact): static
    {
        $this->company_contact = $company_contact;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function __toString() {
        return strip_tags($this->note);
    }
}
