<?php

declare(strict_types=1);

namespace App\Form\Model;

use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

class DoubleFactorAuthenticationSetup
{
    #[Assert\NotBlank]
    public ?User $user = null;

    #[Assert\NotBlank]
    public ?string $code = null;
}
