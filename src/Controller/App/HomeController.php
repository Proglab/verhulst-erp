<?php

declare(strict_types=1);

namespace App\Controller\App;

use App\Entity\User;
use App\Service\InvoiceGetter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Microsoft\Kiota\Authentication\Oauth\ClientCredentialContext;
use Microsoft\Graph\Core\Authentication\GraphPhpLeagueAuthenticationProvider;
use Microsoft\Graph\GraphServiceClient;
use Microsoft\Kiota\Abstractions\ApiException;


#[IsGranted(User::ROLE_USER)]
class HomeController extends AbstractController
{
    public function __construct(private RequestStack $requestStack)
    {
    }

    #[Route('/app', name: 'app')]
    public function testLayout(): Response
    {
        return $this->render('app/dashboard/index.html.twig', [
            'locale' => $this->requestStack->getCurrentRequest()->getLocale(),
            'year' => date('Y'),
        ]);
    }
}