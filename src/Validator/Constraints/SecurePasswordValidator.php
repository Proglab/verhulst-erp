<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class SecurePasswordValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof SecurePassword) {
            throw new UnexpectedTypeException($constraint, SecurePassword::class);
        }

        if (null === $value) {
            return;
        }

        $value = (string) $value;

        if (!preg_match('/^(?=.*[!@#$%^&*-])(?=.*[0-9])(?=.*[A-Z]).{8,}$/', $value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->addViolation();
        }
    }
}
