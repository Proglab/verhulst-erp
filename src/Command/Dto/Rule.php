<?php

namespace App\Command\Dto;

class Rule
{
    public function __construct(public string $RuleType, public string $Clause)
    {

    }
}
