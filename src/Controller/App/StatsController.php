<?php

declare(strict_types=1);

namespace App\Controller\App;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(User::ROLE_ADMIN)]
class StatsController extends AbstractController
{
    #[Route('/app/{_locale}/stats', name: 'app_dashboard_stats')]
    public function index(): Response
    {
        return $this->render('app/dashboard/stats.html.twig');
    }
}
