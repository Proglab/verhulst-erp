<?php

declare(strict_types=1);

namespace App\Command\Dto;

class Listing
{
    public function __construct(public string $title)
    {
    }
}
