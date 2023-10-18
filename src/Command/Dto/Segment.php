<?php

declare(strict_types=1);

namespace App\Command\Dto;

class Segment
{
    public function __construct(public string $Title, public array $RuleGroups = [])
    {
    }
}
