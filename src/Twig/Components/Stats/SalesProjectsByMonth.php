<?php

declare(strict_types=1);

namespace App\Twig\Components\Stats;

use App\Entity\Project;
use App\Entity\User;
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

    #[LiveProp(writable: true)]
    public ?string $date = null;

    #[LiveProp(writable: true)]
    public ?Project $project = null;

    #[LiveProp(writable: true)]
    public array $users = [];

    public function __construct(private Security $security, private BaseSalesRepository $salesRepository, private ChartBuilderInterface $chartBuilder, private UserRepository $userRepository)
    {
        $this->date = (new \DateTime())->format('Y');
    }

    public function getGraph()
    {
        if (null === $this->date || null === $this->project) {
            return null;
        }

        if (!$this->security->isGranted('ROLE_ADMIN')) {
            throw new \Exception('You are not allowed to access this page');
        }

        $months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
        /** @var User $user */
        $datasets = [];

        $i = 0;

        if (empty($this->users)) {
            foreach ($this->userRepository->getCommercials() as $user) {
                ++$i;
                $data = $this->getDatas($user);

                $datasets[] = [
                    'label' => $user->getFullName(),
                    'backgroundColor' => $this->color($i),
                    'borderColor' => $this->color($i),
                    'data' => $data,
                ];
            }
        } else {
            foreach ($this->users as $user) {
                $user = $this->userRepository->find($user);
                ++$i;
                $data = $this->getDatas($user);

                $datasets[] = [
                    'label' => $user->getFullName(),
                    'backgroundColor' => $this->color($i),
                    'borderColor' => $this->color($i),
                    'data' => $data,
                ];
            }
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

    protected function color($i)
    {
        $frequency = 0.4;

        $red = sin($frequency * $i + 0) * 127 + 128;
        $green = sin($frequency * $i + 2) * 127 + 128;
        $blue = sin($frequency * $i + 4) * 127 + 128;

        return "rgb($red, $green, $blue)";
    }

    protected function getDatas(User $user): array
    {
        $sales = $this->salesRepository->getSalesStatsByMonthByUser((int) $this->date, $user, $this->project);
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

        return $datas;
    }
}
