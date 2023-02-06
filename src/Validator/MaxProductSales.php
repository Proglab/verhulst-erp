<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class MaxProductSales extends Constraint
{
    public string $maxSalesExceededMessage = 'Max sales exceeded. Already %d sales on a maximum of %d ';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
