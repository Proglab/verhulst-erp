<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;

/**
 * @group functional
 */
class RegistrationControllerTest extends AbstractControllerTest
{
    public function testRegister(): void
    {
        $crawler = $this->client->request('GET', '/inscription');
        self::assertResponseIsSuccessful();

        $email = sprintf('test%s@mail.fr', (new \DateTime())->getTimestamp());

        $form = $crawler->filter('form[name=registration_form]')->form([
            'registration_form[firstName]' => 'firstname',
            'registration_form[lastName]' => 'lastname',
            'registration_form[email]' => $email,
            'registration_form[plainPassword][first]' => 'Password123!',
            'registration_form[plainPassword][second]' => 'Password123!',
            'registration_form[agreeTerms]' => true,
        ]);

        $this->client->submit($form);
        self::assertResponseRedirects('/connexion');
    }

    public function testRegisterWithNotSecurePassword(): void
    {
        $crawler = $this->client->request('GET', '/inscription');
        self::assertResponseIsSuccessful();

        $email = sprintf('test%s@mail.fr', (new \DateTime())->getTimestamp());

        $form = $crawler->filter('form[name=registration_form]')->form([
            'registration_form[firstName]' => 'firstname',
            'registration_form[lastName]' => 'lastname',
            'registration_form[email]' => $email,
            'registration_form[plainPassword][first]' => 'password',
            'registration_form[plainPassword][second]' => 'password',
            'registration_form[agreeTerms]' => true,
        ]);

        $this->client->submit($form);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertStringContainsString('Votre mot de passe doit suivre les consignes de sécurité', $this->client->getResponse()->getContent());
    }
}
