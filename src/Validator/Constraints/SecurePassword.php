<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

final class SecurePassword extends Constraint
{
    public string $message = 'Your password must follow the security instructions';

    public function validatedBy(): string
    {
        return SecurePasswordValidator::class;
    }
}
