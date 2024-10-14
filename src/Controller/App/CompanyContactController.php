<?php

declare(strict_types=1);

namespace App\Controller\App;

use App\Entity\CompanyContact;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(User::ROLE_USER)]
class CompanyContactController extends AbstractController
{
    public function __construct()
    {
    }

    #[Route('/app/{_locale}/contact/index', name: 'contact_index')]
    public function index(string $_locale): Response
    {
        return $this->render('app/contact/index.html.twig', [
            'locale' => $_locale,
        ]);
    }

    #[Route('/app/{_locale}/contact/{contact}/details', name: 'contact_details')]
    public function detail(string $_locale, CompanyContact $contact): Response
    {
        return $this->render('app/contact/details.html.twig', [
            'locale' => $_locale,
            'contact' => $contact,
        ]);
    }

    #[Route('/app/{_locale}/contact/create', name: 'contact_create')]
    public function create(Request $request, string $_locale): Response
    {
        return $this->render('app/contact/new.html.twig', [
            'locale' => $_locale,
        ]);
    }

    #[Route('/app/{_locale}/contact/{contact}/edit', name: 'contact_edit')]
    public function edit(Request $request, string $_locale, CompanyContact $contact): Response
    {
        return $this->render('app/contact/edit.html.twig', [
            'locale' => $_locale,
            'contact' => $contact,
        ]);
    }
}
