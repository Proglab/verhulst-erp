<?php

declare(strict_types=1);

namespace App\Twig\Components\Dashboard;

use App\Entity\BaseSales;
use App\Entity\User;
use App\Repository\BaseSalesRepository;
use App\Service\SalesService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('last_sales', template: 'app/dashboard/last_sales.html.twig')]
class LastSalesComponent
{
    use DefaultActionTrait;
    public function __construct(private BaseSalesRepository $salesRepository, private Security $security, private SalesService $salesService)
    {
    }

    public function getLastSales(): array
    {
        /** @var User $me */
        $me = $this->security->getUser();

        return $this->salesRepository->get10LastSalesByUser($me);
    }
    #[LiveAction]
    public function validate(#[LiveArg] int $id): void
    {
        $this->salesService->validate($id);
    }

    #[LiveAction]
    public function delete(#[LiveArg] int $id): void
    {
        $this->salesService->delete($id);
    }
}
