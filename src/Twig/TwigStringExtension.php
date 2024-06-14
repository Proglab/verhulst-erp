<?php

declare(strict_types=1);

namespace App\Twig;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TwigStringExtension extends AbstractExtension
{
    public function __construct(
        private readonly HtmlSanitizerInterface $sanitizer,
    ) {
    }

    /**
     * @noRector
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('sanitize', $this->sanitize(...), ['is_safe' => ['html']]),
            new TwigFilter('isEgal', $this->isEgal(...)),
        ];
    }

    /**
     * This method allow nullable value. It will be removed when the official
     * bundle supports the nullable value.
     */
    public function sanitize(?string $html = null): ?string
    {
        return $html ? $this->sanitizer->sanitize($html) : null;
    }

    /**
     * Permet de faire une condition sur l'équivalence entre deux éléments et ajouter une classe si condition OK.
     */
    public function isEgal(string $class, string $id, string $element, string $addClass): string
    {
        if ($id === $element) {
            return $class . ' ' . $addClass;
        }

        return $class;
    }
}
