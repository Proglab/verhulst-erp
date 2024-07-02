<?php

declare(strict_types=1);

namespace App\Controller\App;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(User::ROLE_COMMERCIAL)]
class RecapController extends AbstractController
{
    #[Route('/app/{_locale}/users/recap', name: 'app_users_sales_recap')]
    public function recap(): Response
    {
        return $this->render('app/dashboard/recap/recap.html.twig');
    }
}
