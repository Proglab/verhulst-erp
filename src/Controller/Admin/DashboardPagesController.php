<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardPagesController extends DashboardController
{
    #[Route('/admin/{_locale}', name: 'admin')]
    public function index(): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('admin/dashboard.html.twig');
    }

    #[Route('/admin/{_locale}/dashboard/com', name: 'dashboard_com')]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard_com.html.twig');
    }
}
