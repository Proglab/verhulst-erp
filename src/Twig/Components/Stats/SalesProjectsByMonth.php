<?php

declare(strict_types=1);

namespace App\Twig\Components\Stats;

use App\Entity\Project;
use App\Form\Type\StatsSalesProjectsMonthFilterType;
use App\Repository\BaseSalesRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('stats_sales_projects_month', template: 'app/dashboard/stats/sales_projects_month.html.twig')]
class SalesProjectsByMonth extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp(writable: true, format: 'Y-m-d')]
    public ?\DateTime $date_begin = null;

    #[LiveProp(writable: true, format: 'Y-m-d')]
    public ?\DateTime $date_end = null;

    #[LiveProp(writable: true)]
    public ?Project $project = null;

    #[LiveProp(writable: true)]
    public array $users = [];

    public function __construct(private Security $security, private BaseSalesRepository $salesRepository, private ChartBuilderInterface $chartBuilder, private UserRepository $userRepository)
    {
        $this->date_end = (new \DateTime());
        $this->date_begin = (new \DateTime())->sub(new \DateInterval('P1Y'));
    }

    public function getGraph(): ?Chart
    {
        if (null === $this->project) {
            return null;
        }

        if (!$this->security->isGranted('ROLE_ADMIN')) {
            throw new \Exception('You are not allowed to access this page');
        }

        $dateBegin = clone $this->date_begin;
        $dateBegin->modify('first day of this month');
        $dateEnd = clone $this->date_end;
        $dateEnd->modify('last day of this month');
        $interval = new \DateInterval('P1M');
        $period = new \DatePeriod($dateBegin, $interval, $dateEnd);

        $months = [];

        foreach ($period as $date) {
            $months[] = $date->format('Y-m');
        }

        $datasets = [];

        $dateBegin = clone $this->date_begin;
        $dateEnd = clone $this->date_end;

        if (empty($this->users)) {
            $users = $this->userRepository->getCommercials();
        } else {
            $users = [];
            foreach ($this->users as $user) {
                $users[] = $this->userRepository->find($user);
            }
        }

        $sales = $this->salesRepository->getSalesStatsByDateByUser($dateBegin, $dateEnd, $this->project, $users, $months);

        $i = 0;
        foreach ($sales as $user_id => $sale) {
            $datasets[] = [
                'label' => $this->userRepository->find($user_id)->getFullName(),
                'backgroundColor' => $this->color($i),
                'borderColor' => $this->color($i),
                'data' => $sale,
            ];

            ++$i;
        }

        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);

        $chart->setData([
            'labels' => $months,
            'datasets' => $datasets,
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

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(StatsSalesProjectsMonthFilterType::class);
    }

    protected function color(int $i): string
    {
        $frequency = 0.4;

        $red = sin($frequency * $i + 0) * 127 + 128;
        $green = sin($frequency * $i + 2) * 127 + 128;
        $blue = sin($frequency * $i + 4) * 127 + 128;

        return "rgb($red, $green, $blue)";
    }
}
