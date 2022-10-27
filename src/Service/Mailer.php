<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

class Mailer
{
    public function __construct(
        private TranslatorInterface $translator
    ) {
    }

    public function sendRegistrationConfirmationMail(User $user): TemplatedEmail
    {
        return (new TemplatedEmail())
            ->to(new Address($user->getEmail(), $user->getFullname()))
            ->subject($this->translator->trans('email.user.account_confirmation.subject'))
            ->htmlTemplate('email/security/registration_confirmation.html.twig')
            ->context([
                'user' => $user,
            ]);
    }
}
