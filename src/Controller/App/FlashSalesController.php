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
            $tempPrice = empty($sale->getForecastPrice()) ? '-' : number_format($sale->getForecastPrice() * $sale->getQuantity(), 2, ',', '') . '€';

            $data->data[] = [
                '<h6 class="mb-0">'.$sale->getName().'</h6><i>Numéro de PO : '.$sale->getPo().'</i>',
                '<h6 class="mb-0">'.$sale->getContact()->getFullName().'</h6><i>'. (empty($sale->getContact()->getCompany()) ? '-' : $sale->getContact()->getCompany()->getName()).'</i>',
                '<h6 class="mb-0">'.$price.'€</h6><i>Quantité : '.$sale->getQuantity().'</i>',
                '<h6 class="mb-0">Prix final : '.number_format($sale->getPrice() * $sale->getQuantity(), 2, ',', '').'€</h6><i>Prix temporaire : '.$tempPrice.'</i>',
                '<h6 class="mb-0">'.number_format($price * $sale->getQuantity() * $sale->getPercentVr() / 100, 2, ',', '') .'€'.'</h6><i>'.$sale->getPercentVr().'%</i>',
                '<h6 class="mb-0">'.number_format($price * $sale->getQuantity() * $sale->getPercentCom() / 100, 2, ',', '') .'€'.'</h6><i>'.$sale->getPercentCom().'%</i>',
                '<h6 class="mb-0">'.$sale->getDate()->format('d/m/Y').'</h6>',
                $sale->isValidate() ? '<i class="fa-solid fa-check text-success"></i>' : '<i class="fa-solid fa-xmark text-danger"></i>',
                '<div class="dropdown dropstart">
                    <a href="javascript:void(0)" class="text-muted" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        <iconify-icon icon="solar:menu-dots-bold"></iconify-icon>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton" style="">
                        '.(($sale->isValidate())?'<li>
                            <a href="'.$this->generateUrl('sales_flash_create').'" class="dropdown-item d-flex align-items-center gap-3">
                                <i class="fa-solid fa-pen-to-square"></i> Modifier
                            </a>
                        </li>
                        <li>
                            <a data-action="live#action" data-live-action-param="validate" class="dropdown-item d-flex align-items-center gap-3">
                                <i class="fa-solid fa-check"></i> Valider
                            </a>
                        </li>':'').'
                        <li>
                            <a href="'.$this->generateUrl('sales_flash_create').'" class="dropdown-item d-flex align-items-center gap-3">
                                <i class="fa-solid fa-trash"></i> Suprimer
                            </a>
                        </li>
                    </ul>
                </div>',
            ];
        }

        return $this->json($data);
    }
}