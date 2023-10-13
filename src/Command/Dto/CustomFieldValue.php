<?php

namespace App\Command\Dto;

class CustomFieldValue
{
    public function __construct(public string $Key, public ?string $Value = null)
    {

    }
}