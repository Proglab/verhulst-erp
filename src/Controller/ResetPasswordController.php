<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\Model\ResetPasswordModel;
use App\Form\Type\ResetPasswordRequestFormType;
use App\Form\Type\ResetPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Route('/reinitialiser-mon-mot-de-passe')]
class ResetPasswordController extends BaseController
{
    use ResetPasswordControllerTrait;

    public function __construct(
        private readonly ResetPasswordHelperInterface $resetPasswordHelper,
        private readonly MailerInterface $mailer,
        private readonly TranslatorInterface $translator,
        private readonly UserPasswordHasherInterface $hasher,
        private readonly EntityManagerInterface $manager,
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

        return $this->render('security/reset_password/request.html.twig', [
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
    public function reset(Request $request, ?string $token = null): Response
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
            $this->addFlash('danger', new TranslatableMessage('reset_password.reset.flash_error', [
                '%message%' => $this->translator->trans(\sprintf('reset_password.errors.%s', $e->getReason())),
            ]));

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
                $this->translator->trans('reset_password.reset.flash_success'),
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password/reset.html.twig', [
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
            $this->addFlash('reset_password_error', \sprintf(
                '%s - %s',
                $this->translator->trans(ResetPasswordExceptionInterface::MESSAGE_PROBLEM_HANDLE, [], 'ResetPasswordBundle'),
                $this->translator->trans(\sprintf('reset_password.errors.%s', $e->getReason()))
            ));

            return $this->redirectToRoute('app_forgot_password_request');
        }

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
            ])
        ;

        $this->mailer->send($email);

        // Store the token object in session for retrieval in check-email route.
        $this->setTokenObjectInSession($resetToken);

        return $this->redirectToRoute('app_check_email');
    }
}
