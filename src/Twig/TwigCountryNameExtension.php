<?php

namespace App\Twig;

use Symfony\Component\Intl\Countries;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TwigCountryNameExtension extends AbstractExtension
{

    public function getFilters(): array
    {
        return [
            new TwigFilter('countryFullName', $this->countryFullName(...)),
        ];
    }


    public function countryFullName(string $countryIso)
    {
        return Countries::getName($countryIso);
    }

}