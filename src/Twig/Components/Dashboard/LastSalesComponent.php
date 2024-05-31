<?php

namespace App\Twig\Components\Dashboard;

use App\Entity\User;
use App\Repository\BaseSalesRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('last_sales', template: 'app/dashboard/last_sales.html.twig')]
class LastSalesComponent
{
    public function __construct(private BaseSalesRepository $salesRepository, private Security $security)
    {
    }
    public function getLastSales()
    {
        /** @var User $me */
        $me = $this->security->getUser();
        return $this->salesRepository->get10LastSalesByUser($me);
    }

}