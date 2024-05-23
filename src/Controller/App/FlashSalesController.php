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
            $price = empty($sale->getPrice()) ? $sale->getForecastPrice() : $sale->getPrice();
            $tempPrice = empty($sale->getForecastPrice()) ? '-' : $sale->getForecastPrice();

            $validateButton = $sale->isValidate() ? '' : '<a href="'.$this->generateUrl('sales_flash_create').'" class="btn btn-success" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Tooltip on top"><i class="fa-solid fa-check"></i></a>';
            $editButton = $sale->isValidate() ? '' : '<a href="'.$this->generateUrl('sales_flash_create').'" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Tooltip on top"><i class="fa-solid fa-pen-to-square"></i></a>';


            $data->data[] = [
                '<b>'.$sale->getName().'</b><br><i>PO : '.$sale->getPo().'</i>',
                'Prix unitaire : '.$price.'€<br><i>Quantité : '.$sale->getQuantity().'</i>',
                ($price * $sale->getQuantity() * $sale->getPercentVr() / 100) .'€'.'<br>'.$sale->getPercentVr().'%',
                ($price * $sale->getQuantity() * $sale->getPercentCom() / 100) .'€'.'<br>'.$sale->getPercentCom().'%',
                'Prix temporaire : '.($sale->getForecastPrice() * $sale->getQuantity()).'€'.'<br>Prix final : '.($sale->getPrice() * $sale->getQuantity()).'€',
                $sale->getDate()->format('d/m/Y'),
                $sale->isValidate() ? '<i class="fa-solid fa-check text-success"></i>' : '<i class="fa-solid fa-xmark text-danger"></i>',
                '<div class="text-end">'.$editButton.' '.$validateButton.'
                <a href="'.$this->generateUrl('sales_flash_create').'" class="btn btn-danger" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Tooltip on top"><i class="fa-solid fa-trash"></i></a></div>',
            ];
        }

        return $this->json($data);
    }
}