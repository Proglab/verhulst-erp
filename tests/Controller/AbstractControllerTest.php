<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;

abstract class AbstractControllerTest extends WebTestCase
{
    use Factories;

    protected KernelBrowser $client;

    public static function setUpBeforeClass(): void
    {
        StaticDriver::setKeepStaticConnections(false);
    }

    public static function tearDownAfterClass(): void
    {
        StaticDriver::setKeepStaticConnections(true);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->client->request('GET', '/deconnexion');
    }

    protected function responseIsSuccessful(string $url): void
    {
        $this->client->request('GET', $url);
        self::assertResponseIsSuccessful();
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     */
    protected function refreshEntity(object $entity): void
    {
        $this->manager->refresh($entity);
    }

    protected function assertSelectorEx(string $selector): void
    {
        self::assertGreaterThanOrEqual(1, $this->client->getCrawler()->filter($selector)->count());
    }

    protected function assertSelectorNotEx(string $selector): void
    {
        self::assertEquals(0, $this->client->getCrawler()->filter($selector)->count());
    }

    protected function submitLogin(string $email, string $password): void
    {
        $crawler = $this->client->request('GET', '/connexion');
        self::assertResponseIsSuccessful();
        $this->client->enableProfiler();

        $form = $crawler->selectButton('Connexion')->form();
        $form['email'] = $email;
        $form['password'] = $password;
        $this->client->submit($form);
    }
}
