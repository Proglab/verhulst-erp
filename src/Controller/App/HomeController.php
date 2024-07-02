<?php

declare(strict_types=1);

namespace App\Controller\App;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(User::ROLE_USER)]
class HomeController extends AbstractController
{
    public function __construct(private RequestStack $requestStack)
    {
    }

    #[Route('/app/{_locale}', name: 'app')]
    public function app(Request $request): Response
    {
        return $this->render('app/dashboard/index.html.twig', [
            'locale' => $this->requestStack->getCurrentRequest()->getLocale(),
            'year' => date('Y'),
        ]);
    }

    #[Route('/app', name: 'app_home')]
    public function appHome(): Response
    {
        return $this->redirect($this->generateUrl('app', ['_locale' => $this->requestStack->getCurrentRequest()->getLocale()]));
    }
}
