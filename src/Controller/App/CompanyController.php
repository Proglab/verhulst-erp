<?php

declare(strict_types=1);

namespace App\Controller\App;

use App\Entity\Project;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(User::ROLE_USER)]
class CompanyController extends AbstractController
{
    public function __construct()
    {
    }

    #[Route('/app/{_locale}/company/index', name: 'company_index')]
    public function index(string $_locale): Response
    {
        return $this->render('app/company/index.html.twig', [
            'locale' => $_locale,
        ]);
    }
}
