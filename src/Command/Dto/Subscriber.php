<?php

declare(strict_types=1);

namespace App\Command\Dto;

class Subscriber
{
    public function __construct(public string $EmailAddress, public string $Name, public array $CustomFields = [], public string $ConsentToTrack = 'Yes')
    {
    }
}
