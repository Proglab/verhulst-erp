<?php

namespace App\Command\Dto;

class Subscriber
{
    public function __construct(public string $EmailAddress, public string $Name, public array $CustomFields = [], public string $ConsentToTrack = "Yes")
    {

    }
}