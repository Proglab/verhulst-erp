<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;

class Mailer
{
    public function sendRegistrationConfirmationMail(User $user, bool $freeTrialValidationEnabled = false): TemplatedEmail
    {
        return (new TemplatedEmail())
            ->to(new Address($user->getEmail(), $user->getFullname()))
            ->subject('Confirmation de votre inscription')
            ->htmlTemplate('email/security/registration_confirmation.html.twig')
            ->context([
                'user' => $user,
                'freeTrialValidationEnabled' => $freeTrialValidationEnabled,
            ]);
    }
}
