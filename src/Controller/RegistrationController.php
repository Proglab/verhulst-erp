<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\AppCustomAuthenticator;
use App\Security\EmailVerifier;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends BaseController
{
    public function __construct(
        private readonly EmailVerifier $emailVerifier,
        private readonly Mailer $mailer,
        private readonly TranslatorInterface $translator,
        private readonly RateLimiterFactory $accountCreationLimiter,
        private readonly bool $disableRateLimiters,
    ) {
    }

    #[Route('/inscription', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (false === $this->disableRateLimiters) {
                $limiter = $this->accountCreationLimiter->create($request->getClientIp());

                if (false === $limiter->consume()->isAccepted()) {
                    $this->addCustomFlash(
                        'login_flash',
                        'danger',
                        $this->translator->trans('register.rate_limiter')
                    );

                    return $this->redirectToRoute('app_register');
                }
            }

            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            )
                ->setLocale($request->getLocale());

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user

            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                $this->mailer->sendRegistrationConfirmationMail($user)
            );
            // do anything else you need here, like send an email

            $this->addCustomFlash(
                'login_flash',
                'success',
                $this->translator->trans('register.flash_message_success')
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, UserRepository $userRepository, UserAuthenticatorInterface $userAuthenticator, AppCustomAuthenticator $authenticator): Response
    {
        if ($this->isGranted(User::ROLE_USER)) {
            $this->redirectToRoute('home');
        }

        $id = $request->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if ((null === $user) || ($user->getEnabled() && $user->getVerified())) {
            return $this->redirectToRoute('home');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addCustomFlash(
                'login_flash',
                'danger',
                $this->translator->trans($exception->getReason(), [], 'VerifyEmailBundle')
            );

            return $this->redirectToRoute('app_login');
        }
        $this->addCustomFlash(
            'toast',
            'success',
            $this->translator->trans('register.verified_account_message_success')
        );

        return $userAuthenticator->authenticateUser(
            $user,
            $authenticator,
            $request
        );
    }
}
