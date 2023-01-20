<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Commission;
use App\Entity\Company;
use App\Entity\Project;
use App\Entity\Sales;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

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
