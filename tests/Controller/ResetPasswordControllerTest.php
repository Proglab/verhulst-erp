<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

final class ResetPasswordControllerTest extends AbstractControllerTest
{
    private ResetPasswordHelperInterface $resetPasswordHelper;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetPasswordHelper = self::getContainer()->get(ResetPasswordHelperInterface::class);
        $this->userRepository = self::getContainer()->get(UserRepository::class);
    }

    public function testRequestResetPassword(): void
    {
        $crawler = $this->client->request('GET', '/reinitialiser-mon-mot-de-passe');
        $form = $crawler->selectButton('Envoyer')->form([
            'reset_password_request_form[email]' => 'admin@admin.fr',
        ]);

        $this->client->submit($form);
        self::assertResponseRedirects('/reinitialiser-mon-mot-de-passe/verification-email', Response::HTTP_FOUND);
    }

    public function testResetPassword(): void
    {
        /** @var User $user */
        $user = $this->userRepository->findOneBy(['email' => 'gonzague@verhulst.be']);
        $resetToken = $this->resetPasswordHelper->generateResetToken($user);

        $this->client->request('GET', \sprintf('/reinitialiser-mon-mot-de-passe/reinitialiser/%s', $resetToken->getToken()));
        self::assertResponseRedirects('/reinitialiser-mon-mot-de-passe/reinitialiser');
        $crawler = $this->client->followRedirect();

        self::assertSelectorExists('.app-reset-password');
        $form = $crawler->filter('form[name=reset_password]')->form([
            'reset_password[plainPassword][first]' => 'TestMyNewPassword123!',
            'reset_password[plainPassword][second]' => 'TestMyNewPassword123!',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects('/connexion');
        $this->client->followRedirect();
        self::assertSelectorExists('.alert-success');

        $this->submitLogin($user->getEmail(), 'TestMyNewPassword123!');
        self::assertTrue($this->getSecurityDataCollector()->isAuthenticated());

        /** @var User $user */
        $user = $this->userRepository->findOneBy(['email' => 'gonzague@verhulst.be']);
        $user->setPassword('Password123!');
        $this->manager->flush();
    }
}
