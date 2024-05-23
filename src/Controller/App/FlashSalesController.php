<?php

namespace App\Controller\App;

use App\Entity\CompanyContact;
use App\Entity\FastSales;
use App\Entity\User;
use App\Repository\FastSalesRepository;
use App\Repository\SalesRepository;
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

    #[Route('/app/{_locale}/sales/flash', name: 'sales_flash_index')]
    public function index(FastSalesRepository $salesRepository)
    {
        return $this->render('app/sales/flash/index.html.twig', [
            'sales' => $salesRepository->findBy(['user' => $this->getUser()], ['date' => 'DESC']),
        ]);
    }

    #[Route('/api/sales', name: 'sales_flash_ajax')]
    public function api_sales(FastSalesRepository $salesRepository)
    {
        $sales = $salesRepository->findBy([], ['date' => 'DESC']);

        $data = new \stdClass();
        $data->data = [];

        /** @var FastSales $sale */
        foreach ($sales as $sale) {
            $data->data[] = [
                $sale->getId(),
                $sale->getName(),
                $sale->getDate()->format('d/m/Y'),
                $sale->getContact()->getFullName(),
                $sale->getContact()->getFullName(),
                $sale->isValidate() ? '<iconify-icon icon="el:ok" class="text-success" width="25" height="25"></iconify-icon>' : '<iconify-icon icon="svg-spinners:clock" class="text-danger" width="25" height="25"></iconify-icon>',
            ];
        }

        return $this->json($data);
    }
}