<?php

declare(strict_types=1);

namespace App\Entity\Trait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait SlugTrait
{
    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    #[Assert\Regex(pattern: '/^([a-z0-9-]+)$/', message: 'Un slug ne peut contenir que des lettres minuscules, des chiffres et des tirets.')]
    private ?string $slug = null;

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }
}
