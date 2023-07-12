<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;

class AppCrawlerTest extends AbstractControllerTest
{

    private $urls = ['#', '', '/deconnexion', 'http://localhost/admin/fr?crudAction=index&crudControllerFqcn=App%5CController%5CAdmin%5CCommissionCrudController', 'http://localhost/admin/fr?crudAction=generatePdf&crudControllerFqcn=App%5CController%5CAdmin%5CSalesBdcCrudController'];

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
        $this->checkUrl($this->urls, $client, $this->url);
    }

    public function testCrawlLinksConnectedAsAdmin()
    {
        $user = $this->userRepository->findOneBy(['email' => 'gonzague@verhulst.be']);
        $this->client->loginUser($user);

        $client = $this->client;

        $this->url = '/admin/fr';
        self::bootKernel();
        $this->checkUrl($this->urls, $client, $this->url);
    }

    public function testCrawlLinksConnectedAsCommercial()
    {
        $user = $this->userRepository->findOneBy(['email' => 'cedric@verhulst.be']);
        $this->client->loginUser($user);

        $client = $this->client;
        $this->url = '/admin/fr';
        self::bootKernel();
        $this->checkUrl($this->urls, $client, $this->url);
    }

    public function testCrawlLinksConnectedAsCompta()
    {
        $user = $this->userRepository->findOneBy(['email' => 'compta@verhulst.be']);
        $this->client->loginUser($user);

        $client = $this->client;

        $this->url = '/admin/fr';
        self::bootKernel();
        $this->checkUrl($this->urls, $client, $this->url);
    }

    public function testCrawlLinksConnectedAsEncodeur()
    {
        $user = $this->userRepository->findOneBy(['email' => 'encodeur@verhulst.be']);
        $this->client->loginUser($user);

        $client = $this->client;

        $this->url = '/admin/fr';
        self::bootKernel();
        $this->checkUrl($this->urls, $client, $this->url);
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
                if (str_starts_with($param, 'referrer') || str_starts_with($param, 'page') || str_starts_with($param, 'sort')) {
                    unset($params[$i]);
                }
                if (str_starts_with($param, 'entityId')) {
                    $params[$i] = 'entityId=0';
                }
                if (str_starts_with($param, 'year')) {
                    $params[$i] = 'year=0';
                }
                if (str_starts_with($param, 'month')) {
                    $params[$i] = 'month=0';
                }
                ++$i;
            }
            $urlGeneric = implode('&', $params);
            if (!\in_array($urlGeneric, $urls, true)) {
                if (
                    (str_starts_with($url, 'http')
                      && !str_contains($url, 'localhost'))
                      || str_contains($url, '.pdf')
                      || str_contains($url, 'javascript:')
                      || str_contains($url, 'tel:')
                      || str_contains($url, 'batchDelete')
                      || str_contains($url, 'resetPassword')
                      || str_contains($url, 'mailto:')) {
                    continue;
                }
                $urls[] = $urlGeneric;
                $this->checkUrl($urls, $client, $url);
            }
        }
    }
}
