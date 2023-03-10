<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

/**
 * @group functional
 */
class SecurityControllerTest extends AbstractControllerTest
{
    protected ResetPasswordHelperInterface $resetPasswordHelper;
    protected UserPasswordHasherInterface $hasher;
    protected UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = self::getContainer()->get(UserRepository::class);
        $this->resetPasswordHelper = self::getContainer()->get(ResetPasswordHelperInterface::class);
        $this->hasher = self::getContainer()->get(UserPasswordHasherInterface::class);
    }

    public function testDisplayLogin(): void
    {
        $this->client->request('GET', '/connexion');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testLoginWithBadCredentials(): void
    {
        $crawler = $this->client->request('GET', '/connexion');

        $form = $crawler->selectButton('Connexion')->form([
            'email' => 'badEmai@mail.fr',
            'password' => 'fakePassword',
        ]);

        $this->client->submit($form);
        self::assertResponseRedirects('/connexion', Response::HTTP_FOUND);
        $this->client->followRedirect();
        $this->assertSelectorEx('.alert.alert-danger');
    }

    public function testSuccessfullLogin(): void
    {
        $this->submitLogin('gonzague@verhulst.be', 'Password123!');

        self::assertResponseRedirects('/admin', Response::HTTP_FOUND);
        $this->client->followRedirect();
        self::assertResponseRedirects('/admin/fr', Response::HTTP_FOUND);
    }

    public function testUpdatePassword(): void
    {
        $user = $this->userRepository->findOneBy(['email' => 'fabrice@insideweb.be']);
        $user->setPassword('Password123!');
        $this->manager->flush();
        self::assertEquals($user->getPassword(), 'Password123!');

        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/admin/fr?routeName=admin_password_update');
        $form = $crawler->filter('form[name=password_update]')->form([
            'password_update[oldPassword]' => 'Password123!',
            'password_update[newPassword][first]' => 'MyNewPassword123!',
            'password_update[newPassword][second]' => 'MyNewPassword123!',
        ]);

        $this->client->submit($form);

        $user = $this->userRepository->findOneBy(['email' => 'fabrice@insideweb.be']);
        self::assertEquals($user->getPassword(), 'MyNewPassword123!');
        $user->setPassword('Password123!');
        $this->manager->flush();
    }

    public function testDisplay2faActivationPage(): void
    {
        $user = $this->userRepository->findOneBy(['email' => 'fabrice@insideweb.be']);
        $this->client->loginUser($user);
        $this->client->request('GET', '/authentification-2-facteurs');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testDisplay2faAuthPage(): void
    {
        $user = $this->userRepository->findOneBy(['email' => 'fabrice@insideweb.be']);
        $user->setIsTotpEnabled(true)
            ->setTotpSecret('totpsecret');
        $this->manager->flush();
        $user = $this->userRepository->findOneBy(['email' => 'fabrice@insideweb.be']);
        $this->submitLogin($user->getEmail(), 'Password123!');
        $this->client->followRedirect();
        $this->assertSelectorEx('#login_double_authentication');

        $user = $this->userRepository->findOneBy(['email' => 'fabrice@insideweb.be']);
        $user->setIsTotpEnabled(false)
            ->setTotpSecret(null);
        $this->manager->flush();
    }
}
