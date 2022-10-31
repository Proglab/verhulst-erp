<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ResendConfirmationEmailRequest;
use App\Entity\User;
use App\Form\Model\DoubleFactorAuthenticationSetup;
use App\Form\Model\PasswordUpdate;
use App\Form\Type\DoubleFactorAuthenticationSetupType;
use App\Form\Type\PasswordUpdateType;
use App\Repository\ResendConfirmationEmailRequestRepository;
use App\Security\EmailVerifier;
use App\Security\Voter\ExtraVoter;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\Builder;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
        private readonly UserPasswordHasherInterface $hasher,
        private readonly TranslatorInterface $translator,
        private readonly CacheInterface $cache,
        private readonly TotpAuthenticatorInterface $totpAuthenticator,
        private readonly array $enabledLocales,
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
    #[IsGranted(data: ExtraVoter::IS_XML_HTTP_REQUEST, subject: 'request')]
    #[Route(path: '/renvoyer-confirmation-email/{token}', name: 'app_resend_confirmation_email')]
    public function resendConfirmationEmailAction(string $token = null): RedirectResponse
    {
        if (null === $token) {
            $this->addCustomFlash(
                'login_flash',
                'danger',
                'Le lien est invalide. Veuillez réessayer.'
            );

            return $this->redirectToRoute('app_login');
        }

        /** @var ResendConfirmationEmailRequest $resendConfirmationEmailRequest */
        $resendConfirmationEmailRequest = $this->resendConfirmationEmailRequestRepository->findToken($token);

        $user = $resendConfirmationEmailRequest->getUser();
        $limiter = $this->resendConfirmationEmailLimiter->create($user->getEmail());

        if (false === $limiter->consume()->isAccepted()) {
            $this->addCustomFlash(
                'login_flash',
                'danger',
                'Vous avez déjà demandé un nouvel e-mail de confirmation pour votre compte. Veuillez vérifier votre e-mail ou réessayez dans 5 minutes.'
            );

            return $this->redirectToRoute('app_login');
        }

        switch (true) {
            case $resendConfirmationEmailRequest->getUser()->getEnabled() && $resendConfirmationEmailRequest->getUser()->getVerified():
                $this->addCustomFlash(
                    'login_flash',
                    'danger',
                    'Votre compte est déjà activé.'
                );
                break;

            case $resendConfirmationEmailRequest->isExpired():
                $this->addCustomFlash(
                    'login_flash',
                    'danger',
                    'Le lien est expiré ! Veuillez réessayer !'
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
                    'Un nouvel e-mail de confirmation vient de vous être envoyé !'
                );
                break;
        }

        return $this->redirectToRoute('app_login');
    }

    #[IsGranted(data: User::ROLE_USER, message: 'Vous devez être authentifié pour accéder à cette page !')]
    #[Route(path: '/modifier-mon-mot-de-passe', name: 'app_password_update')]
    public function updatePassword(Request $request): RedirectResponse|Response
    {
        $user = $this->getUser();
        $passwordUpdate = new PasswordUpdate();
        $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $passwordUpdate->newPassword;
            $hash = $this->hasher->hashPassword($user, $newPassword);
            $user->setPassword($hash);

            $this->manager->flush();

            $this->addCustomFlash(
                'toast',
                'success',
                $this->translator->trans('global.confirmation_message')
            );

            return $this->redirectToRoute('home');
        }

        return $this->renderForm('security/update_password.html.twig', [
            'form' => $form,
        ]);
    }

    #[IsGranted(data: User::ROLE_USER)]
    #[Route(path: '/authentification-2-facteurs', name: 'app_2fa_enable')]
    public function enable2fa(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user->isTotpAuthenticationEnabled()) {
            $totpAuthenticator = $this->totpAuthenticator;
            $totpCode = $this->cache->get(sprintf('2fa_activation_totp_%s', $user->getEmail()),
                function (ItemInterface $item) use ($totpAuthenticator) {
                    $item->expiresAfter(900);

                    return $totpAuthenticator->generateSecret();
                });
            $user->setTotpSecret($totpCode);
            $this->manager->flush();
        }

        $setup = new DoubleFactorAuthenticationSetup();
        $setup->user = $user;
        $form = $this->createForm(DoubleFactorAuthenticationSetupType::class, $setup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setIsTotpEnabled(true);
            $this->manager->flush();
            $this->cache->delete(sprintf('2fa_activation_totp_%s', $user->getEmail()));
            $this->cache->delete(sprintf('2fa_activation_qr_code_%s', $user->getEmail()));
            $this->addCustomFlash(
                'toast',
                'success',
                $this->translator->trans('2fa.enable.success_message')
            );

            return $this->redirectToRoute('home');
        }

        return $this->renderForm('security/2fa/enable2fa.html.twig', [
            'form' => $form,
        ]);
    }

    #[IsGranted(data: User::ROLE_USER)]
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
            $this->translator->trans('2fa.disabled_success_message')
        );

        return $this->redirectToRoute('home');
    }

    #[IsGranted(data: User::ROLE_USER)]
    #[Route(path: '/authentification-2-facteurs/qr-code', name: 'app_qr_code')]
    public function displayGoogleAuthenticatorQrCode(): Response
    {
        $user = $this->getUser();

        return $this->cache->get(sprintf('2fa_activation_qr_code_%s', $user->getEmail()),
            function (ItemInterface $item) use ($user) {
                $item->expiresAfter(900);
                $qrCodeContent = $this->totpAuthenticator->getQRContent($user);
                $result = Builder::create()
                    ->data($qrCodeContent)
                    ->build();

                return new Response($result->getString(), Response::HTTP_OK, ['Content-Type' => 'image/png']);
            });
    }
}
