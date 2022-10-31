<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Valid2faActivationCode extends Constraint
{
    public string $message = 'The verification code is invalid';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
