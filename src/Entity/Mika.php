<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\PrimaryKeyTrait;
use App\Repository\CssRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CssRepository::class)]
class Mika
{
    use PrimaryKeyTrait;

    #[ORM\Column(length: 37, nullable: true)]
    protected ?string $name = null;

    #[ORM\Column(length: 45, nullable: true)]
    protected ?string $email = null;

    #[ORM\Column(length: 6, nullable: true)]
    protected ?string $lang = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getLang(): ?string
    {
        return $this->lang;
    }

    public function setLang(?string $lang): void
    {
        $this->lang = $lang;
    }
}
