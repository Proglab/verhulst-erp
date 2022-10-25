<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\ResendConfirmationEmailRequest;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

use function sprintf;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function __construct(
        private EntityManagerInterface $manager,
        private UrlGeneratorInterface $urlGenerator
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
     * @throws Exception
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
                ->setExpiresAt(new DateTimeImmutable(sprintf('+%d days', 7)));

            $this->manager->persist($resendConfirmationEmailRequest);
            $this->manager->flush();

            $resetConfirmationEmailUrl = $this->urlGenerator->generate('app_resend_confirmation_email', [
                'token' => $resendConfirmationEmailRequest->getHashedToken(),
            ]);

            throw new CustomUserMessageAccountStatusException(sprintf("Votre compte n'est pas vérifié. Veuillez confirmer votre inscription
                en cliquant sur le lien qui vous a été envoyé par email.
                Pensez à vérifier dans vos spams.
                <a href='%s' class='js-submit'>Renvoyer l'email de confirmation</a>", $resetConfirmationEmailUrl));
        }
    }
}
