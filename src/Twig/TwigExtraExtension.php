<?php

declare(strict_types=1);

namespace App\Twig;

use App\Helper\LocaleHelper;
use App\Helper\MoneyHelper;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigExtraExtension extends AbstractExtension
{
    public function __construct(
        private readonly string $publicDir,
        private readonly UrlGeneratorInterface $generator,
        private readonly RequestStack $request,
        private readonly EntrypointLookupInterface $entrypointLookup,
        private readonly array $enabledLocales,
    ) {
    }

    /**
     * @noRector
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('countryName', $this->countryName(...)),
            new TwigFilter('isEgal', $this->isEgal(...)),
            new TwigFilter('equalsString', $this->equalsString(...)),
            new TwigFilter('moneyFormat', $this->getMoneyFormat(...)),
            new TwigFilter('badge', $this->badgeFilter(...), ['is_safe' => ['html']]),
            new TwigFilter('minimizeString', $this->minimizeString(...), ['is_safe' => ['html']]),
            new TwigFilter('toLocaleName', $this->toLocaleName(...)),
        ];
    }

    /**
     * @noRector
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('encore_entry_css_source', $this->getEncoreEntryCssSource(...)),
            new TwigFunction('login_target_path', $this->loginTargetPath(...)),
            new TwigFunction('getLocales', $this->getLocales(...)),
        ];
    }

    public function minimizeString(string $text, int $max = 30): string
    {
        if ($text && \strlen($text) > $max) {
            return sprintf('%s...', substr($text, 0, $max - 3));
        }

        return $text;
    }

    public function countryName(string $countryIso): string
    {
        return \Locale::getDisplayRegion('-' . $countryIso, 'fr');
    }

    public function loginTargetPath(): string
    {
        $masterRequest = $this->request->getMainRequest();

        if ($masterRequest) {
            return $this->generator->generate('app_login', ['redirect' => $masterRequest->getRequestUri()]);
        }

        return $this->generator->generate('app_login');
    }

    public function getEncoreEntryCssSource(string $entryName): string
    {
        $files = $this->entrypointLookup->getCssFiles($entryName);

        $source = '';

        foreach ($files as $file) {
            $source .= file_get_contents($this->publicDir . $file);
        }

        return $source;
    }

    public function getMoneyFormat(float|int|null $number): ?string
    {
        return MoneyHelper::format($number);
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

    public function equalsString(string $current, string $expected, ?string $onTrue = null, ?string $onFalse = null): ?string
    {
        if ($current === $expected) {
            return $onTrue;
        }

        return $onFalse;
    }

    /**
     * Retourne un badge.
     */
    public function badgeFilter(string $content, array $options = []): string
    {
        $defaultOptions = [
            'color' => 'success',
            'rounded' => false,
        ];

        $options = array_merge($defaultOptions, $options);

        $color = $options['color'];
        $pill = $options['rounded'] ? 'rounded-pill' : '';

        $template = '<span class="badge bg-%s %s">%s</span>';

        return sprintf(
            $template,
            $color,
            $pill,
            $content
        );
    }

    public function toLocaleName(string $locale): string
    {
        return LocaleHelper::getLanguageName($locale);
    }

    public function getLocales(): array
    {
        $locales = [];

        foreach ($this->enabledLocales as $locale) {
            $locales[$locale] = LocaleHelper::getLanguageName($locale);
        }

        return $locales;
    }
}
