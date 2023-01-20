<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecapController extends DashboardController
{
    /**
    public function __construct(private EntityManagerInterface $entityManager, private AdminUrlGenerator $adminUrlGenerator)
    {
    }
     */
    #[Route('/admin/{_locale}/recap', name: 'app_admin_recap')]
    public function recap(): Response
    {
        return $this->render('admin/recap/recap.html.twig', [
        ]);
    }

    #[Route('/admin/{_locale}/myrecap', name: 'app_admin_myrecap')]
    public function myRecap(): Response
    {
        return $this->render('admin/recap/myrecap.html.twig', [
        ]);
    }
}
