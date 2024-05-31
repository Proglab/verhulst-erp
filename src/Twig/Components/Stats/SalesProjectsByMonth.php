<?php

declare(strict_types=1);

namespace App\Twig\Components\Stats;

use App\Entity\BaseSales;
use App\Entity\Company;
use App\Entity\CompanyContact;
use App\Entity\Product;
use App\Entity\Project;
use App\Entity\User;
use App\Form\Type\SalesRecapFilters;
use App\Form\Type\StatsSalesProjectsMonthFilterType;
use App\Repository\BaseSalesRepository;
use App\Repository\CompanyRepository;
use App\Repository\ProjectRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\ValidatableComponentTrait;
use Symfony\Component\Validator\Constraints as Assert;

#[AsLiveComponent('stats_sales_projects_month', template: 'app/dashboard/stats/sales_projects_month.html.twig')]
class SalesProjectsByMonth extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp(writable: true)]
    public ?\DateTime $date = null;

    #[LiveProp(writable: true)]
    public ?Project $project = null;

    #[LiveProp(writable: true)]
    public ?User $user = null;

    public function __construct(private Security $security, private BaseSalesRepository $salesRepository, private ChartBuilderInterface $chartBuilder)
    {
        $this->date = new \DateTime();
        $this->user = $this->security->getUser();
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(StatsSalesProjectsMonthFilterType::class);
    }
    public function getGraph()
    {
        if ($this->date === null || $this->project === null || $this->user === null) {
            return null;

        }

        if (!$this->security->isGranted('ROLE_COMMERCIAL')) {
            throw new \Exception('You are not allowed to access this page');
        }

        $months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

        $me = $this->user;
        $sales = $this->salesRepository->getCommissionsStatsByMonthByUser((int) $this->date->format('Y'), $me);
        $datas = [];
        foreach ($sales as $sale) {
            $month_index = (int) explode('-', $sale['date'])[0];
            $year = explode('-', $sale['date'])[1];
            $datas[$month_index - 1] = $sale['price'];
        }

        for ($i = 0; $i < 12; ++$i) {
            if (!isset($datas[$i])) {
                $datas[$i] = '0.00';
            }
        }
        ksort($datas);

        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);

        $chart->setData([
            'labels' => $months,
            'datasets' => [
                [
                    'label' => 'Commissions',
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => $datas,
                ],
            ],
        ]);
        $chart->setOptions([
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 100,
                ],
            ],
        ]);

        return $chart;
    }

}
