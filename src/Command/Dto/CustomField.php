<?php

declare(strict_types=1);

namespace App\Command\Dto;

class CustomField
{
    public function __construct(public string $FieldName, public string $DataType)
    {
    }
}
