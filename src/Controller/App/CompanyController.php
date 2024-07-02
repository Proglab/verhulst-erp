<?php

declare(strict_types=1);

namespace App\Controller\App;

use App\Entity\Project;
use App\Entity\User;
use App\Repository\CompanyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(User::ROLE_USER)]
class CompanyController extends AbstractController
{
    public function __construct(private CompanyRepository $companyRepository)
    {
    }

    #[Route('/app/{_locale}/company/index', name: 'company_index')]
    public function index(string $_locale): Response
    {
        return $this->render('app/company/index.html.twig', [
            'locale' => $_locale,
        ]);
    }


    #[Route('/app/{_locale}/company/{id}/details', name: 'company_details')]
    public function detail(string $_locale, int $id): Response
    {
        $company = $this->companyRepository->find($id);
        return $this->render('app/company/details.html.twig', [
            'locale' => $_locale,
            'company' => $company,
        ]);
    }
}
