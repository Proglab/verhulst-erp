<?php

declare(strict_types=1);

namespace App\Helper;

class MoneyHelper
{
    public static function format(null|float|int $number): ?string
    {
        if (null === $number) {
            return null;
        }

        $formattedNumber = (float) str_replace(',', '.', (string) $number);

        return number_format($formattedNumber, 2, ',', ' ');
    }
}
