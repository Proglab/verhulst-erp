<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;

/**
 * @group functional
 */
class SecurityControllerTest extends AbstractControllerTest
{
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

//    public function testSuccessfullLogin(): void
//    {
//        $this->submitLogin('user@user.fr', 'Password123!');
//
//        self::assertResponseRedirects('/', Response::HTTP_FOUND);
//    }
}
