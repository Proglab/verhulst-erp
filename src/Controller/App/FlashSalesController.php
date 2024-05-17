<?php

namespace App\Controller\App;

use App\Entity\CompanyContact;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(User::ROLE_USER)]
class FlashSalesController extends AbstractController
{
    #[Route('/app/{_locale}/sales/flash/create/step1', name: 'sales_flash_create')]
    public function create()
    {
        return $this->render('app/sales/flash/create.html.twig');
    }

    #[Route('/app/{_locale}/sales/flash/create/sale/{contact}', name: 'sales_flash_create_sale')]
    public function createSale(CompanyContact $contact)
    {
        return $this->render('app/sales/flash/create_sale.html.twig', [
            'contact' => $contact,
        ]);
    }
}