<?php

declare(strict_types=1);

namespace App\Twig\Components\Company;

use App\Form\Type\FlashSalesFilterType;
use App\Repository\CompanyRepository;
use App\Repository\FastSalesRepository;
use App\Service\SalesService;
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

#[AsLiveComponent('company-index', template: 'app/company/component_index.html.twig')]
class CompanyIndex
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public ?string $query = null;
    #[LiveProp(writable: true)]
    public int $page = 1;

    public function __construct(
        private CompanyRepository $companyRepository,
        private PaginatorInterface $paginator,
    ) {
    }

    public function companies()
    {
        $qb = $this->companyRepository->search($this->query);
        return $this->paginator->paginate($qb, $this->page, 12);

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


}
