<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\ResendConfirmationEmailRequest;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserChecker implements UserCheckerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $manager,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
        }

        // user is deleted, show a generic Account Not Found message.
        // if ($user->isDeleted()) {
        //     throw new AccountDeletedException();
        // }
    }

    /**
     * @throws \Exception
     */
    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            throw new AccountExpiredException();
        }

        if (!$user->getEnabled() && !$user->getVerified()) {
            $resendConfirmationEmailRequest = new ResendConfirmationEmailRequest();
            $resendConfirmationEmailRequest->setHashedToken(rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '='))
                ->setUser($user)
                ->setExpiresAt(new \DateTimeImmutable(sprintf('+%d days', 7)));

            $this->manager->persist($resendConfirmationEmailRequest);
            $this->manager->flush();

            $resetConfirmationEmailUrl = $this->urlGenerator->generate('app_resend_confirmation_email', [
                'token' => $resendConfirmationEmailRequest->getHashedToken(),
            ]);

            throw new CustomUserMessageAccountStatusException($this->translator->trans('resend_confirmation_link.exception', ['%path%' => $resetConfirmationEmailUrl]));
        }
    }
}
