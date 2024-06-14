<?php

declare(strict_types=1);

namespace App\Twig\Components\FlashSales;

use App\Form\Type\FlashSalesFilterType;
use App\Repository\FastSalesRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('flash-sale-index', template: 'app/sales/flash/flash_sale_index.html.twig')]
class FlashSaleIndex extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp(writable: true, format: 'Y-m-d')]
    public ?\DateTime $min = null;
    #[LiveProp(writable: true, format: 'Y-m-d')]
    public ?\DateTime $max = null;
    #[LiveProp(writable: true)]
    public ?array $users = [];
    #[LiveProp(writable: true, url: true)]
    public int $page = 1;

    public function __construct(private FastSalesRepository $fastSalesRepository, private PaginatorInterface $paginator)
    {
    }

    public function getFastSales(): PaginationInterface
    {
        $qb = $this->fastSalesRepository->findByFilters($this->min, $this->max, $this->users);
        $paginator = $this->paginator->paginate($qb, $this->page, 10);

        return $paginator;
    }

    #[LiveAction]
    public function validate(#[LiveArg] int $id): void
    {
        $sale = $this->fastSalesRepository->find($id);
        $sale->setValidate(true);
        $this->fastSalesRepository->save($sale, true);
    }

    #[LiveAction]
    public function delete(#[LiveArg] int $id): void
    {
        $sale = $this->fastSalesRepository->find($id);
        $this->fastSalesRepository->remove($sale, true);
    }

    #[LiveAction]
    public function previousPage(): void
    {
        --$this->page;
    }

    #[LiveAction]
    public function nextPage(): void
    {
        ++$this->page;
    }

    #[LiveAction]
    public function gotoPage(#[LiveArg] int $page): void
    {
        $this->page = $page;
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(FlashSalesFilterType::class);
    }
}
