<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;

class AppCrawlerTest extends AbstractControllerTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = self::getContainer()->get(UserRepository::class);
    }

    public function testCrawlLinks()
    {
        $client = $this->client;

        $urls = ['#', ''];

        $this->url = '/';
        $this->checkUrl($urls, $client, $this->url);
    }

    public function testCrawlLinksConnectedAsAdmin()
    {
        $user = $this->userRepository->findOneBy(['email' => 'gonzague@verhulst.be']);
        $this->client->loginUser($user);

        $client = $this->client;

        $urls = ['#', '', '/deconnexion', 'http://localhost/admin/fr?crudAction=index&crudControllerFqcn=App%5CController%5CAdmin%5CCommissionCrudController'];

        $this->url = '/admin/fr';
        self::bootKernel();
        $this->checkUrl($urls, $client, $this->url);
    }

    public function testCrawlLinksConnectedAsCommercial()
    {
        $user = $this->userRepository->findOneBy(['email' => 'cedric@verhulst.be']);
        $this->client->loginUser($user);

        $client = $this->client;

        $urls = ['#', '', '/deconnexion', 'http://localhost/admin/fr?crudAction=index&crudControllerFqcn=App%5CController%5CAdmin%5CCommissionCrudController'];

        $this->url = '/admin/fr';
        self::bootKernel();
        $this->checkUrl($urls, $client, $this->url);
    }

    public function testCrawlLinksConnectedAsCompta()
    {
        $user = $this->userRepository->findOneBy(['email' => 'compta@verhulst.be']);
        $this->client->loginUser($user);

        $client = $this->client;

        $urls = ['#', '', '/deconnexion', 'http://localhost/admin/fr?crudAction=index&crudControllerFqcn=App%5CController%5CAdmin%5CCommissionCrudController'];

        $this->url = '/admin/fr';
        self::bootKernel();
        $this->checkUrl($urls, $client, $this->url);
    }

    public function testCrawlLinksConnectedAsEncodeur()
    {
        $user = $this->userRepository->findOneBy(['email' => 'encodeur@verhulst.be']);
        $this->client->loginUser($user);

        $client = $this->client;

        $urls = ['#', '', '/deconnexion', 'http://localhost/admin/fr?crudAction=index&crudControllerFqcn=App%5CController%5CAdmin%5CCommissionCrudController'];

        $this->url = '/admin/fr';
        self::bootKernel();
        $this->checkUrl($urls, $client, $this->url);
    }

    protected function checkUrl(&$urls, $client, $url)
    {
        $crawler = $client->request('GET', $url);

        if (Response::HTTP_OK !== $client->getResponse()->getStatusCode()) {
            self::assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode(), $url . ' - FROM - ' . $this->url);
        }

        $this->url = $url;
        foreach ($crawler->filter('a') as $link) {
            $url = $link->getAttribute('href');

            $params = explode('&', $url);
            $i = 0;
            foreach ($params as $param) {
                if (0 === strpos($param, 'referrer') || 0 === strpos($param, 'page') || 0 === strpos($param, 'sort')) {
                    unset($params[$i]);
                }
                if (0 === strpos($param, 'entityId')) {
                    $params[$i] = 'entityId=0';
                }
                if (0 === strpos($param, 'year')) {
                    $params[$i] = 'year=0';
                }
                if (0 === strpos($param, 'month')) {
                    $params[$i] = 'month=0';
                }
                ++$i;
            }
            $urlGeneric = implode('&', $params);
            if (!\in_array($urlGeneric, $urls, true)) {
                if (
                    (0 === strpos($url, 'http') &&
                    false === strpos($url, 'localhost')) ||
                    false !== strpos($url, '.pdf') ||
                    false !== strpos($url, 'javascript:') ||
                    false !== strpos($url, 'tel:') ||
                    false !== strpos($url, 'batchDelete') ||
                    false !== strpos($url, 'resetPassword') ||
                    false !== strpos($url, 'mailto:')) {
                    continue;
                }
                $urls[] = $urlGeneric;
                $this->checkUrl($urls, $client, $url);
            }
        }
    }
}
