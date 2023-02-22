<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private TranslatorInterface $translator,
        private MailerInterface $mailer
    ) {

    }

    public function processSendingPasswordResetEmail(User $user): void
    {
        $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        $locale = $user->getLocale();

        $subject = $this->translator->trans('email.user.reset_password.subject', [], 'messages', $locale);

        $email = (new TemplatedEmail())
            ->to(new Address($user->getEmail(), $user->getFullName()))
            ->subject($subject)
            ->htmlTemplate('email/user/reset_password.html.twig')
            ->context([
                'resetToken' => $resetToken,
                'locale' => $locale,
                'user' => $user,
            ]);
        $this->mailer->send($email);
    }
}