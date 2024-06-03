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
use App\Form\Type\StatsSalesProjectsYearFilterType;
use App\Repository\BaseSalesRepository;
use App\Repository\CompanyRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
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

#[AsLiveComponent('stats_sales_projects_year', template: 'app/dashboard/stats/sales_projects_year.html.twig')]
class SalesProjectsByYear extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp(writable: true)]
    public ?string $date = null;

    #[LiveProp(writable: true)]
    public ?Project $project = null;

    #[LiveProp(writable: true)]
    public array $users = [];

    public function __construct(private Security $security, private BaseSalesRepository $salesRepository, private ChartBuilderInterface $chartBuilder, private UserRepository $userRepository, private ProjectRepository $projectRepository)
    {
        $this->date = (new \DateTime())->format('Y');
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(StatsSalesProjectsYearFilterType::class);
    }
    public function getGraph()
    {
        if ($this->date === null) {
            return null;
        }

        if (!$this->security->isGranted('ROLE_ADMIN')) {
            throw new \Exception('You are not allowed to access this page');
        }

        $projects = $this->projectRepository->findAllByYear((int) $this->date);
        $labels = [];
        foreach ($projects as $project) {
            $labels[] = strtolower($project->getName());
        }

        $datasets = [];

        $i = 0;
        if (empty($this->users)) {
            foreach ($this->userRepository->getCommercials() as $user) {
                $i++;

                $data = $this->getDatas((int) $this->date, $user, $projects);
                $dataset = [];
                foreach ($data as $d) {
                    $dataset[] = $d['price'];
                }


                $datasets[] = [
                    'label' => $user->getFullName(),
                    'backgroundColor' => $this->color($i),
                    'borderColor' => $this->color($i),
                    'data' => $dataset,
                ];
            }
        } else {
            foreach ($this->users as $user) {
                $user = $this->userRepository->find($user);
                $i++;
                $data = $this->getDatas((int) $this->date, $user, $projects);
                $dataset = [];
                foreach ($data as $d) {
                    $dataset[] = $d['price'];
                }
                $datasets[] = [
                    'label' => $user->getFullName(),
                    'backgroundColor' => $this->color($i),
                    'borderColor' => $this->color($i),
                    'data' => $dataset,
                ];
            }
        }


        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);

        $chart->setData([
            'labels' => $labels,
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

    protected function color($i)
    {
        $frequency = 0.4;

           $red   = sin($frequency*$i + 0) * 127 + 128;
           $green = sin($frequency*$i + 2) * 127 + 128;
           $blue  = sin($frequency*$i + 4) * 127 + 128;

            return "rgb($red, $green, $blue)";
    }

    protected function getDatas(int $date, User $user, array $projects): array
    {
        $sales = $this->salesRepository->getSalesStatsByYearByUser((int) $this->date, $user, $projects);
        return $sales;
    }

}
