<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Form\Model\DoubleFactorAuthenticationSetup;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class Valid2faActivationCodeValidator extends ConstraintValidator
{
    public function __construct(private readonly TotpAuthenticatorInterface $totpAuthenticator)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof Valid2faActivationCode) {
            throw new UnexpectedTypeException($constraint, Valid2faActivationCode::class);
        }

        if (!$value instanceof DoubleFactorAuthenticationSetup) {
            return;
        }

        if (!$this->totpAuthenticator->checkCode($value->user, $value->code)) {
            $this->context->buildViolation($constraint->message)
                ->atPath('code')
                ->addViolation();
        }
    }
}
