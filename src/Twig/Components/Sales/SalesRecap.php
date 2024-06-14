<?php

declare(strict_types=1);

namespace App\Twig\Components\Sales;

use App\Entity\BaseSales;
use App\Entity\Company;
use App\Entity\CompanyContact;
use App\Entity\Product;
use App\Entity\ProductPackageVip;
use App\Entity\ProductSponsoring;
use App\Entity\Project;
use App\Entity\User;
use App\Form\Type\SalesRecapFilters;
use App\Repository\BaseSalesRepository;
use App\Repository\CompanyRepository;
use App\Repository\ProjectRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\ValidatableComponentTrait;

#[AsLiveComponent('sales_recap', template: 'app/sales/componentsRecap.html.twig')]
class SalesRecap extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;
    use ValidatableComponentTrait;

    #[LiveProp(writable: true, format: 'Y-m-d')]
    #[Assert\Valid]
    #[Assert\Date]
    public ?\DateTime $from = null;

    #[LiveProp(writable: true, format: 'Y-m-d')]
    #[Assert\Valid]
    #[Assert\Date]
    public ?\DateTime $to = null;

    #[LiveProp(writable: true)]
    public ?Project $project = null;

    #[LiveProp(writable: true)]
    public ?Product $product = null;

    #[LiveProp(writable: true)]
    public ?Company $company = null;

    #[LiveProp(writable: true)]
    public ?CompanyContact $contact = null;

    #[LiveProp(writable: true)]
    public ?User $user = null;

    #[LiveProp(writable: true)]
    public ?string $archive = '0';

    #[LiveProp(writable: true, url: true)]
    public int $page = 1;

    public function __construct(private BaseSalesRepository $salesRepository, private ProjectRepository $projectRepository, private CompanyRepository $companyRepository, private PaginatorInterface $paginator)
    {
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

    public function getSales(): PaginationInterface
    {
        $qb = $this->salesRepository->searchQb(
            from : $this->from,
            to : $this->to,
            project : empty($this->formValues['project']) ? null : $this->projectRepository->find($this->formValues['project']),
            product : $this->product,
            company : empty($this->formValues['company']) ? null : $this->companyRepository->find($this->formValues['company']),
            contact : $this->contact,
            user : $this->user,
            archive : 'all' === $this->archive ? null : (bool) $this->archive,
        );

        return $this->paginator->paginate($qb, $this->page, 10);
    }

    public function getTotalSales(): float
    {
        return $this->salesRepository->searchTotal(
            from : $this->from,
            to : $this->to,
            project : empty($this->formValues['project']) ? null : $this->projectRepository->find($this->formValues['project']),
            product : $this->product,
            company : empty($this->formValues['company']) ? null : $this->companyRepository->find($this->formValues['company']),
            contact : $this->contact,
            user : $this->user,
            archive : 'all' === $this->archive ? null : (bool) $this->archive,
        );
    }

    public function getTotalSalesCom(): float
    {
        return $this->salesRepository->searchTotalCom(
            from : $this->from,
            to : $this->to,
            project : empty($this->formValues['project']) ? null : $this->projectRepository->find($this->formValues['project']),
            product : $this->product,
            company : empty($this->formValues['company']) ? null : $this->companyRepository->find($this->formValues['company']),
            contact : $this->contact,
            user : $this->user,
            archive : 'all' === $this->archive ? null : (bool) $this->archive,
        );
    }

    public function getTotalSalesVr(): float
    {
        return $this->salesRepository->searchTotalVr(
            from : $this->from,
            to : $this->to,
            project : empty($this->formValues['project']) ? null : $this->projectRepository->find($this->formValues['project']),
            product : $this->product,
            company : empty($this->formValues['company']) ? null : $this->companyRepository->find($this->formValues['company']),
            contact : $this->contact,
            user : $this->user,
            archive : 'all' === $this->archive ? null : (bool) $this->archive,
        );
    }

    #[LiveAction]
    public function export(): RedirectResponse
    {
        $datas = $this->salesRepository->search(
            from : $this->from,
            to : $this->to,
            project : empty($this->formValues['project']) ? null : $this->projectRepository->find($this->formValues['project']),
            product : $this->product,
            company : empty($this->formValues['company']) ? null : $this->companyRepository->find($this->formValues['company']),
            contact : $this->contact,
            user : $this->user,
            archive : 'all' === $this->archive ? null : (bool) $this->archive,
        );

        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $worksheet = $spreadsheet->getActiveSheet();

        $worksheet->getCell('A1')->setValue('Date de la vente');
        $worksheet->getCell('B1')->setValue('Projet');
        $worksheet->getCell('C1')->setValue('Produit');
        $worksheet->getCell('D1')->setValue('Société');
        $worksheet->getCell('E1')->setValue('Contact');
        $worksheet->getCell('F1')->setValue('Vendeur');
        $worksheet->getCell('G1')->setValue('Quantité');
        $worksheet->getCell('H1')->setValue('Prix final');
        $worksheet->getCell('I1')->setValue('Com sale');
        $worksheet->getCell('J1')->setValue('Com VR');
        $worksheet->getCell('K1')->setValue('Marge net VR');

        /**
         * @var int       $key
         * @var BaseSales $data
         */
        foreach ($datas as $key => $data) {
            /** @var ProductSponsoring|ProductPackageVip|null $product */
            $product = $data->getProduct();


            $worksheet->getCell('A' . ($key + 2))->setValue($data->getDate()->format('d/m/Y'));
            $worksheet->getCell('B' . ($key + 2))->setValue(!empty($product) && !empty($product->getProject()) ? $product->getProject()->getName() : '-');
            $worksheet->getCell('C' . ($key + 2))->setValue(!empty($product) ? $product->getName() : '-');
            $worksheet->getCell('D' . ($key + 2))->setValue(!empty($data->getContact()->getCompany()) ? $data->getContact()->getCompany()->getName() : '-');
            $worksheet->getCell('E' . ($key + 2))->setValue($data->getContact()->getFullName());
            $worksheet->getCell('F' . ($key + 2))->setValue($data->getUser()->getFullName());
            $worksheet->getCell('G' . ($key + 2))->setValue($data->getQuantity());
            $worksheet->getCell('H' . ($key + 2))->setValue($data->getPrice());
            $worksheet->getCell('I' . ($key + 2))->setValue($data->getPercentCom() / 100 * $data->getPrice());
            $worksheet->getCell('J' . ($key + 2))->setValue($data->getPercentVr() / 100 * $data->getPrice());
            $worksheet->getCell('K' . ($key + 2))->setValue('=J' . ($key + 2) . '-I' . ($key + 2));
        }

        $id = uniqid();

        $writer->save($id . '.xls');

        return $this->redirectToRoute('download_file', ['filename' => $id . '.xls']);
    }

    #[LiveAction]
    public function initForm(): void
    {
        $this->resetForm();
        $this->from = null;
        $this->to = null;
        $this->form->get('from')->setData(null);
        $this->form->get('to')->setData(null);
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(SalesRecapFilters::class);
    }
}
