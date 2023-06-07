<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TodoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TodoRepository::class)]
class Todo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $date_reminder = null;

    #[ORM\ManyToOne(inversedBy: 'todos')]
    #[ORM\JoinColumn(nullable: true)]
    private ?CompanyContact $client = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $todo = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_done = null;

    #[ORM\Column]
    private ?bool $done = false;

    #[ORM\ManyToOne(inversedBy: 'todos')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'todos')]
    private ?Project $project = null;

    #[ORM\ManyToOne(inversedBy: 'todos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TodoType $type = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHourReminder(): ?\DateTimeInterface
    {
        return $this->date_reminder;
    }

    public function setHourReminder(?\DateTimeInterface $date_reminder): self
    {
        $hour = '00:00:00';
        if (null !== $date_reminder) {
            $hour = $date_reminder->format('H:i:s');
        }

        $date = $this->date_reminder->format('Y-m-d');
        $this->date_reminder = new \DateTime($date . ' ' . $hour);

        return $this;
    }

    public function getDateReminder(): ?\DateTimeInterface
    {
        return $this->date_reminder;
    }

    public function setDateReminder(\DateTimeInterface $date_reminder): self
    {
        $this->date_reminder = $date_reminder;

        return $this;
    }

    public function getClient(): ?CompanyContact
    {
        return $this->client;
    }

    public function setClient(?CompanyContact $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getTodo(): ?string
    {
        return $this->todo;
    }

    public function setTodo(string $todo): self
    {
        $this->todo = $todo;

        return $this;
    }

    public function getDateDone(): ?\DateTimeInterface
    {
        return $this->date_done;
    }

    public function setDateDone(?\DateTimeInterface $date_done): self
    {
        $this->date_done = $date_done;

        return $this;
    }

    public function isDone(): ?bool
    {
        return $this->done;
    }

    public function setDone(bool $done): self
    {
        $this->done = $done;

        if (null === $this->getDateDone()) {
            $this->setDateDone(new \DateTime());
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getTimeRemaining(): \DateInterval|string
    {
        if ($this->isDone()) {
            return '-';
        }

        return $this->getDateReminder()->diff(new \DateTime('now'));
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function getType(): ?TodoType
    {
        return $this->type;
    }

    public function setType(?TodoType $type): self
    {
        $this->type = $type;

        return $this;
    }
}
