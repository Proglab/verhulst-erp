<?php

declare(strict_types=1);

namespace App\Controller\App;

use App\Entity\CompanyContact;
use App\Entity\FastSales;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(User::ROLE_USER)]
class FlashSalesController extends AbstractController
{
    #[Route('/app/{_locale}/sales/flash/create/step1', name: 'sales_flash_create')]
    public function create(): Response
    {
        return $this->render('app/sales/flash/create.html.twig');
    }

    #[Route('/app/{_locale}/sales/flash/create/sale/{contact}', name: 'sales_flash_create_sale')]
    public function createSale(CompanyContact $contact): Response
    {
        return $this->render('app/sales/flash/create_sale.html.twig', [
            'contact' => $contact,
        ]);
    }

    #[Route('/app/{_locale}/sales/flash', name: 'sales_flash_index')]
    public function index(): Response
    {
        return $this->render('app/sales/flash/index.html.twig');
    }

    #[Route('/app/{_locale}/sales/flash/edit/{sale}', name: 'sales_flash_edit')]
    public function edit(?FastSales $sale): Response
    {
        return $this->render('app/sales/flash/edit.html.twig', [
            'sale' => $sale,
            'contact' => $sale->getContact(),
        ]);
    }
}
