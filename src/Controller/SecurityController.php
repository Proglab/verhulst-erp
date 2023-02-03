<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ResendConfirmationEmailRequest;
use App\Entity\User;
use App\Repository\ResendConfirmationEmailRequestRepository;
use App\Security\EmailVerifier;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\Builder;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends BaseController
{
    public function __construct(
        private readonly EntityManagerInterface $manager,
        private readonly EmailVerifier $emailVerifier,
        private readonly RateLimiterFactory $resendConfirmationEmailLimiter,
        private readonly ResendConfirmationEmailRequestRepository $resendConfirmationEmailRequestRepository,
        private readonly Mailer $mailer,
        private readonly TranslatorInterface $translator,
        private readonly CacheInterface $cache,
        private readonly TotpAuthenticatorInterface $totpAuthenticator,
        private readonly array $enabledLocales,
        private readonly bool $disableRateLimiters,
    ) {
    }

    #[Route(path: '/connexion', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/deconnexion', name: 'app_logout')]
    public function logout(): never
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * Permet à l'utilisateur de modifier la langue du site.
     */
    #[Route(path: '/switch-locale/{locale}', name: 'switch_locale', methods: ['GET'])]
    public function switchLocale(Request $request, ?string $locale = null): Response
    {
        if (!\in_array($locale, $this->enabledLocales, true)) {
            throw new BadRequestHttpException();
        }

        if ($this->getUser() instanceof User) {
            $this->getUser()->setLocale($locale);
            $this->manager->flush();
        }

        $request->getSession()->set('_locale', $locale);
        $redirectUrl = $request->headers->get('referer');

        if (empty($redirectUrl) || str_contains($redirectUrl, '/switch-locale')) {
            $redirectUrl = $this->generateUrl('home');
        }

        return new RedirectResponse($redirectUrl);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    #[Route(path: '/renvoyer-confirmation-email/{token}', name: 'app_resend_confirmation_email')]
    public function resendConfirmationEmailAction(string $token = null): RedirectResponse
    {
        if (null === $token) {
            $this->addCustomFlash(
                'login_flash',
                'danger',
                $this->translator->trans('resend_confirmation_link.token_not_found')
            );

            return $this->redirectToRoute('app_login');
        }

        /** @var ResendConfirmationEmailRequest $resendConfirmationEmailRequest */
        $resendConfirmationEmailRequest = $this->resendConfirmationEmailRequestRepository->findToken($token);

        $user = $resendConfirmationEmailRequest->getUser();

        if (false === $this->disableRateLimiters) {
            $limiter = $this->resendConfirmationEmailLimiter->create($user->getEmail());

            if (false === $limiter->consume()->isAccepted()) {
                $this->addCustomFlash(
                    'login_flash',
                    'danger',
                    $this->translator->trans('resend_confirmation_link.already_resent')
                );

                return $this->redirectToRoute('app_login');
            }
        }

        switch (true) {
            case $resendConfirmationEmailRequest->getUser()->getEnabled() && $resendConfirmationEmailRequest->getUser()->getVerified():
                $this->addCustomFlash(
                    'login_flash',
                    'danger',
                    $this->translator->trans('resend_confirmation_link.already_enabled')
                );
                break;

            case $resendConfirmationEmailRequest->isExpired():
                $this->addCustomFlash(
                    'login_flash',
                    'danger',
                    $this->translator->trans('resend_confirmation_link.expired_link')
                );
                break;

            default:
                /** @var User $user */
                $user = $resendConfirmationEmailRequest->getUser();

                $this->emailVerifier->sendEmailConfirmation(
                    'app_verify_email',
                    $user,
                    $this->mailer->sendRegistrationConfirmationMail($user)
                );

                $this->manager->remove($resendConfirmationEmailRequest);
                $this->manager->flush();

                $this->addCustomFlash(
                    'login_flash',
                    'success',
                    $this->translator->trans('resend_confirmation_link.new_email_sent')
                );
                break;
        }

        return $this->redirectToRoute('app_login');
    }

    #[IsGranted(User::ROLE_USER, message: 'Vous devez être authentifié pour accéder à cette page !')]
    #[Route(path: '/modifier-mon-mot-de-passe', name: 'app_password_update')]
    public function updatePassword(Request $request): RedirectResponse|Response
    {
        return $this->renderForm('security/update_password.html.twig');
    }

    #[IsGranted(User::ROLE_USER)]
    #[Route(path: '/authentification-2-facteurs', name: 'app_2fa_enable')]
    public function enable2fa(Request $request): Response
    {
        return $this->renderForm('security/2fa/enable2fa.html.twig');
    }

    #[IsGranted(User::ROLE_USER)]
    #[Route(path: '/authentification-2-facteurs/desactivation', name: 'app_2fa_disable')]
    public function disable2fa(): Response
    {
        $user = $this->getUser();

        if ($user->isTotpAuthenticationEnabled()) {
            $user->setIsTotpEnabled(false);
            $this->manager->flush();
        }

        $this->addCustomFlash(
            'toast',
            'success',
            $this->translator->trans('2fa.disable.success_message')
        );

        return $this->redirectToRoute('home');
    }

    #[IsGranted(User::ROLE_USER)]
    #[Route(path: '/authentification-2-facteurs/qr-code', name: 'app_qr_code')]
    public function displayGoogleAuthenticatorQrCode(): Response
    {
        $user = $this->getUser();

        return $this->cache->get(sprintf('2fa_activation_qr_code_%s', str_replace('@', '', $user->getEmail())),
            function (ItemInterface $item) use ($user) {
                $item->expiresAfter(900);
                $qrCodeContent = $this->totpAuthenticator->getQRContent($user);
                $result = Builder::create()
                    ->data($qrCodeContent)
                    ->build();

                return new Response($result->getString(), Response::HTTP_OK, ['Content-Type' => 'image/png']);
            });
    }

    #[IsGranted(User::ROLE_USER)]
    #[Route(path: '/mon-profil', name: 'app_update_profile')]
    public function updateProfile(Request $request): Response
    {
        return $this->renderForm('security/update_profile.html.twig');
    }
}
