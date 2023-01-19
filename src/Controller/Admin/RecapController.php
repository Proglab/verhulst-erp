<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecapController extends DashboardController
{

    #[Route('/{_locale}/admin/recap', name: 'app_admin_recap')]
    public function recap(): Response
    {
        return $this->render('admin/recap/recap.html.twig', [
        ]);
    }


    #[Route('/{_locale}/admin/myrecap', name: 'app_admin_myrecap')]
    public function myRecap(): Response
    {
        return $this->render('admin/recap/myrecap.html.twig', [
        ]);
    }
}
