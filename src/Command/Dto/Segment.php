<?php

namespace App\Command\Dto;

class Segment
{
    public function __construct(public string $Title, public array $RuleGroups = [])
    {

    }
}
