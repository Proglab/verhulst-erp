<?php

declare(strict_types=1);

namespace App\Form\Model;

use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Constraints as Assert;

class PasswordUpdate
{
    #[SecurityAssert\UserPassword(message: 'Le mot de passe que vous avez tapé est erroné')]
    public ?string $oldPassword = null;

    #[Assert\Length(min: 4, minMessage: 'Votre mot de passe doit faire au moins 4 caractères')]
    public ?string $newPassword = null;
}
