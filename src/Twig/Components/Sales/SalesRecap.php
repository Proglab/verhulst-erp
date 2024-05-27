<?php

declare(strict_types=1);

namespace App\Twig\Components\Sales;

use App\Entity\CompanyContact;
use App\Entity\FastSales;
use App\Entity\Product;
use App\Entity\Project;
use App\Form\Type\NewFlashSaleType;
use App\Form\Type\SalesRecapFilters;
use App\Repository\FastSalesRepository;
use App\Repository\ProjectRepository;
use App\Repository\SalesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('sales_recap', template: 'app/sales/componentsRecap.html.twig')]
class SalesRecap extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp(writable: true, format: 'Y-m-d')]
    public ?\DateTime $from = null;

    #[LiveProp(writable: true, format: 'Y-m-d')]
    public ?\DateTime $to = null;

    #[LiveProp(writable: true)]
    public ?Project $project = null;

    #[LiveProp(writable: true)]
    public ?Product $product = null;

    #[LiveProp]
    public ?CompanyContact $contact = null;


    public function __construct(private SalesRepository $salesRepository, private ProjectRepository $projectRepository)
    {
    }

    public function getSales(): array
    {
        return $this->salesRepository->search(
            from : $this->from,
            to : $this->to,
            project : empty($this->formValues['project']) ? null : $this->projectRepository->find($this->formValues['project']),
            product : $this->product,
        );
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(SalesRecapFilters::class);
    }
}
