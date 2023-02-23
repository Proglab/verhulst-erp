<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Repository\SalesRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class DashboardPagesController extends DashboardController
{
    public function __construct(private ChartBuilderInterface $chartBuilder, private UserRepository $userRepository, private SalesRepository $salesRepository, private RequestStack $requestStack)
    {
    }

    #[Route('/admin/{_locale}', name: 'dashboard_admin')]
    public function index(): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('dashboard_com');
        }

        $months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
        $default_year = (new \DateTime())->format('Y');
        $year = $this->requestStack->getCurrentRequest()->get('year', $default_year);
        $default_month = (new \DateTime())->format('m');
        $month = $this->requestStack->getCurrentRequest()->get('month', $default_month);

        return $this->render('admin/dashboard.html.twig', [
            'year' => $year,
            'month' => $months[$month - 1],
            'month_num' => $month,
            'locale' => $this->requestStack->getCurrentRequest()->getLocale(),
        ]);
    }

    #[Route('/admin/{_locale}/dashboard/com', name: 'dashboard_com')]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard_com.html.twig');
    }

    #[Route('/admin/{_locale}/dashboard/sales_tot', name: 'sales_tot')]
    public function sales_tot(): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('dashboard_com');
        }

        $months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

        $default_year = (new \DateTime())->format('Y');
        $year = $this->requestStack->getCurrentRequest()->get('year', $default_year);
        $sales = $this->salesRepository->getStatsByMonth((int) $year);
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
                    'label' => 'CA',
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => $datas,
                ],
            ],
        ]);
        $chart->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 100,
                ],
            ],
        ]);

        return $this->render('admin/dashboard/sales_tot.html.twig', [
            'chart' => $chart,
            'year' => $year,
            'locale' => $this->requestStack->getCurrentRequest()->getLocale(),
        ]);
    }

    #[Route('/admin/{_locale}/dashboard/users_tot', name: 'users_tot')]
    public function users_tot(): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('dashboard_com');
        }

        $months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

        $default_year = (new \DateTime())->format('Y');
        $year = $this->requestStack->getCurrentRequest()->get('year', $default_year);
        $default_month = (new \DateTime())->format('m');
        $month = $this->requestStack->getCurrentRequest()->get('month', $default_month);
        $day = (new \DateTime($year . '-' . $month . '-01'))->format('t');
        $chart2 = $this->chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $sales = $this->userRepository->getStatsByUser(new \DateTime($year . '-' . $month . '-01'), new \DateTime($year . '-' . $month . '-' . $day));
        $users_data = [];
        $sales_data = [];
        foreach ($sales as $sale) {
            $users_data[] = $sale['first_name'] . ' ' . $sale['last_name'];
            $sales_data[] = empty($sale['total']) ? 0 : $sale['total'];
        }

        $chart2->setData([
            'labels' => $users_data,
            'datasets' => [
                [
                    'label' => 'Dataset 1',
                    'data' => $sales_data,
                    'borderColor' => 'white',
                    'backgroundColor' => ['#e32727', '#e29f21', '#ffdd31', '#d0d626', '#4f7423', '#6adb28', '#2cd9d1', '#2a395b', '#3539e0', '#a736de'],
                ],
            ],
        ]);

        return $this->render('admin/dashboard/users_tot.html.twig', [
            'chart2' => $chart2,
            'year' => $year,
            'month' => $months[$month - 1],
            'month_num' => $month,
            'locale' => $this->requestStack->getCurrentRequest()->getLocale(),
        ]);
    }
}
