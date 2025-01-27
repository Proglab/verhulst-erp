<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\PrimaryKeyTrait;
use App\Repository\CssRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CssRepository::class)]
class Css
{
    use PrimaryKeyTrait;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $content = null;

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }
}
