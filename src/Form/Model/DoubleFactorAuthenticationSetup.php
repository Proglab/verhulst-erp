<?php

declare(strict_types=1);

namespace App\Form\Model;

use App\Entity\User;
use App\Validator\Constraints\Valid2faActivationCode;
use Symfony\Component\Validator\Constraints as Assert;

#[Valid2faActivationCode]
class DoubleFactorAuthenticationSetup
{
    #[Assert\NotBlank]
    public ?User $user = null;

    #[Assert\NotBlank]
    public ?string $code = null;
}
