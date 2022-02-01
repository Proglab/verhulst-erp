<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

final class SecurePassword extends Constraint
{
    public string $message = 'Votre mot de passe doit suivre les consignes de sécurité';

    public function validatedBy(): string
    {
        return SecurePasswordValidator::class;
    }
}
