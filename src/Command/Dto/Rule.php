<?php

declare(strict_types=1);

namespace App\Command\Dto;

class Rule
{
    public function __construct(public string $RuleType, public string $Clause)
    {
    }
}
