<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigClassNameExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_class', [$this, 'getName']),
        ];
    }

    public function getName(object $object): string
    {
        return (new \ReflectionClass($object))->getShortName();
    }
}
