<?php

declare(strict_types=1);

namespace App\Twig\Components\Dashboard;

use App\Entity\User;
use App\Repository\BaseSalesRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('coms_graph', template: 'app/dashboard/coms_graph.html.twig')]
class ComGraphComponent
{
    use DefaultActionTrait;
    #[LiveProp]
    public string $locale;

    #[LiveProp(writable: true)]
    public int $year;

    public function __construct(private BaseSalesRepository $salesRepository, private Security $security, private ChartBuilderInterface $chartBuilder)
    {
    }

    public function getSalesGraph(): Chart
    {
        if (!$this->security->isGranted('ROLE_COMMERCIAL')) {
            throw new \Exception('You are not allowed to access this page');
        }

        $months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

        /** @var User $me */
        $me = $this->security->getUser();
        $sales = $this->salesRepository->getCommissionsStatsByMonthByUser((int) $this->year, $me);
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

    #[LiveAction]
    public function nextYear(): void
    {
        ++$this->year;
    }

    #[LiveAction]
    public function prevYear(): void
    {
        --$this->year;
    }
}
