<?php

declare(strict_types=1);

namespace App\Controller\App;

use App\Entity\Company;
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

    #[Route('/app/{_locale}/company/{company}/details', name: 'company_details')]
    public function detail(string $_locale, Company $company): Response
    {
        return $this->render('app/company/details.html.twig', [
            'locale' => $_locale,
            'company' => $company,
        ]);
    }

    #[Route('/app/{_locale}/company/create', name: 'company_create')]
    public function create(Request $request, string $_locale): Response
    {
        return $this->render('app/company/new.html.twig', [
            'locale' => $_locale,
        ]);
    }

    #[Route('/app/{_locale}/company/{company}/edit', name: 'company_edit')]
    public function edit(Request $request, string $_locale, Company $company): Response
    {
        return $this->render('app/company/edit.html.twig', [
            'locale' => $_locale,
            'company' => $company,
        ]);
    }
}
