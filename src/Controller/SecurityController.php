<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ResendConfirmationEmailRequest;
use App\Entity\User;
use App\Form\Model\PasswordUpdate;
use App\Form\Type\PasswordUpdateType;
use App\Repository\ResendConfirmationEmailRequestRepository;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Security\Voter\ExtraVoter;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use function serialize;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class SecurityController extends BaseController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private EmailVerifier $emailVerifier,
        private RateLimiterFactory $resendConfirmationEmailLimiter,
        private ResendConfirmationEmailRequestRepository $resendConfirmationEmailRequestRepository,
        private Mailer $mailer,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $hasher,
    ) {
    }

    /**
     * @Route("/connexion", name="app_login")
     */
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

    /**
     * @Route("/deconnexion", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route(path: '/verification-email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request): Response
    {
        if ($this->isGranted(User::ROLE_USER)) {
            $this->redirectToRoute('home');
        }

        $id = $request->get('id');

        if (null === $id) {
            return $this->redirectToRoute('home');
        }

        $user = $this->userRepository->find($id);

        if ((null === $user) || ($user->getEnabled() && $user->getVerified())) {
            return $this->redirectToRoute('home');
        }

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addCustomFlash(
                'login_flash',
                'danger',
                $exception->getReason()
            );

            return $this->redirectToRoute('app_login');
        }

        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->container->get('security.token_storage')->setToken($token);
        $this->container->get('session')->set('_security_main', serialize($token));

        $this->addCustomFlash(
            'toast',
            'success',
            'Votre inscription a été validée !'
        );

        return $this->redirectToRoute('home');
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    #[IsGranted(data: ExtraVoter::IS_XML_HTTP_REQUEST, subject: 'request')]
    #[Route(path: '/renvoyer-confirmation-email/{token}', name: 'app_resend_confirmation_email')]
    public function resendConfirmationEmailAction(string $token = null, Request $request): RedirectResponse
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
                'Mot de passe modifié !'
            );

            return $this->redirectToRoute('home');
        }

        return $this->renderForm('security/update_password.html.twig', [
            'form' => $form,
        ]);
    }
}
