<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\Model\ResetPasswordModel;
use App\Form\Type\ResetPasswordRequestFormType;
use App\Form\Type\ResetPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use function sprintf;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Route('/reinitialiser-mon-mot-de-passe')]
class ResetPasswordController extends BaseController
{
    use ResetPasswordControllerTrait;

    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private MailerInterface $mailer,
        private TranslatorInterface $translator,
        private UserPasswordHasherInterface $hasher,
        private EntityManagerInterface $manager,
    ) {
    }

    /**
     * Display & process form to request a password reset.
     *
     * @throws TransportExceptionInterface
     */
    #[Route(path: '', name: 'app_forgot_password_request')]
    public function request(Request $request): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->processSendingPasswordResetEmail($form->get('email')->getData());
        }

        return $this->renderForm('security/reset_password/request.html.twig', [
            'requestForm' => $form,
            'element' => 'reset-password',
        ]);
    }

    /**
     * Confirmation page after a user has requested a password reset.
     */
    #[Route(path: '/verification-email', name: 'app_check_email')]
    public function checkEmail(): Response
    {
        if (null === ($resetToken = $this->getTokenObjectFromSession())) {
            return $this->redirectToRoute('app_forgot_password_request');
        }

        return $this->render('security/reset_password/check_email.html.twig', [
            'resetToken' => $resetToken,
            'element' => 'reset-password',
        ]);
    }

    /**
     * Validates and process the reset URL that the user clicked in their email.
     */
    #[Route(path: '/reinitialiser/{token}', name: 'app_reset_password')]
    public function reset(Request $request, string $token = null): Response
    {
        if ($token) {
            // We store the token in session and remove it from the URL, to avoid the URL being
            // loaded in a browser and potentially leaking the token to 3rd party JavaScript.
            $this->storeTokenInSession($token);

            return $this->redirectToRoute('app_reset_password');
        }

        $token = $this->getTokenFromSession();

        if (null === $token) {
            throw $this->createNotFoundException("Aucun jeton de réinitialisation du mot de passe trouvé dans l'URL ou dans la session.");
        }

        try {
            /** @var User $user */
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash('reset_password_error', sprintf(
                'Un problème est survenu lors de la validation de votre demande de réinitialisation - %s',
                $this->translator->trans($e->getReason())
            ));

            return $this->redirectToRoute('app_forgot_password_request');
        }

        // The token is valid; allow the user to change their password.
        $resetPasswordModel = new ResetPasswordModel();
        $form = $this->createForm(ResetPasswordType::class, $resetPasswordModel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // A password reset token should be used only once, remove it.
            $this->resetPasswordHelper->removeResetRequest($token);

            // Encode the plain password, and set it.
            $encodedPassword = $this->hasher->hashPassword(
                $user,
                $resetPasswordModel->plainPassword
            );

            $user->setPassword($encodedPassword);

            $this->manager->flush();

            // The session is cleaned up after the password has been changed.
            $this->cleanSessionAfterReset();

            $this->addCustomFlash(
                'login_flash',
                'success',
                'Votre mot de passe a été réinitialisé ! Vous pouvez maintenant vous connecter.'
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->renderForm('security/reset_password/reset.html.twig', [
            'resetForm' => $form,
            'element' => 'reset-password',
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function processSendingPasswordResetEmail(string $emailFormData): RedirectResponse
    {
        $user = $this->manager->getRepository(User::class)->findOneBy([
            'email' => $emailFormData,
        ]);

        // Do not reveal whether a user account was found or not.
        if (!$user) {
            return $this->redirectToRoute('app_check_email');
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash('reset_password_error', sprintf(
                'Un problème est survenu lors du traitement de votre demande de réinitialisation de mot de passe - %s',
                $this->translator->trans($e->getReason())
            ));

            return $this->redirectToRoute('app_forgot_password_request');
        }

        $email = (new TemplatedEmail())
            ->to(new Address($user->getEmail(), $user->getFullName()))
            ->subject('Réinitialisation de votre mot de passe')
            ->htmlTemplate('email/user/reset_password.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ])
        ;

        $this->mailer->send($email);

        // Store the token object in session for retrieval in check-email route.
        $this->setTokenObjectInSession($resetToken);

        return $this->redirectToRoute('app_check_email');
    }
}
