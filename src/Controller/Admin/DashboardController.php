<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Command\StreamedOutput;
use App\Entity\Commission;
use App\Entity\Company;
use App\Entity\CompanyContact;
use App\Entity\Css;
use App\Entity\ProductDivers;
use App\Entity\ProductEvent;
use App\Entity\ProductPackageVip;
use App\Entity\ProductSponsoring;
use App\Entity\Project;
use App\Entity\Sales;
use App\Entity\SalesBdc;
use App\Entity\Todo;
use App\Entity\TodoType;
use App\Entity\User;
use App\Repository\SalesRepository;
use App\Repository\TodoRepository;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class DashboardController extends AbstractDashboardController
{
    public function __construct(private ChartBuilderInterface $chartBuilder, private UserRepository $userRepository, private SalesRepository $salesRepository, private RequestStack $requestStack, private AdminUrlGenerator $adminUrlGenerator, private RoleHierarchyInterface $roleHierarchy, private TodoRepository $todoRepository)
    {
    }

    #[Route('/admin', name: 'admin')]
    public function admin(): RedirectResponse
    {
        return $this->redirectToRoute('app');
    }

    #[Route('/admin', name: 'app_admin_dashboard_index')]
    public function admin2(): RedirectResponse
    {
        return $this->redirectToRoute('app');
    }

    public function configureAssets(): Assets
    {
        $assets = parent::configureAssets();

        return $assets->addWebpackEncoreEntry('htmx');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<img src="/app-logo.png" />')
            ->renderContentMaximized()
            ->setFaviconPath('build/favicon/favicon.ico')
            ->setLocales([
                'fr' => 'Français',
                'nl' => 'Neederlands',
            ])
            ->renderContentMaximized()
            ->renderSidebarMinimized();
    }

    public function configureMenuItems(): iterable
    {
        /** @var User $user */
        $user = $this->getUser();

        return [
            MenuItem::section('Raccourcis')->setPermission('ROLE_COMMERCIAL'),
            MenuItem::linkToCrud('admin.menu.contact.create', 'fas fa-address-book', CompanyContact::class)
                ->setPermission('ROLE_COMMERCIAL')
                ->setAction(Crud::PAGE_NEW)
                ->setController(CompanyContactCrudController::class)
                ->setQueryParameter('entityFqcn', CompanyContact::class),
            MenuItem::linkToCrud('admin.menu.sales.create', 'fas fa-comments-dollar', Sales::class)->setController(SalesCrudController::class)->setPermission('ROLE_COMMERCIAL')->setAction(Crud::PAGE_NEW),
            MenuItem::linkToRoute('admin.menu.flashsale.create', 'fas fa-clipboard-check', 'sales_flash_create')->setPermission('ROLE_COMMERCIAL'),
            //MenuItem::linkToCrud('admin.menu.event.create', 'fa fa-calendar', ProductEvent::class)->setPermission('ROLE_COMMERCIAL')->setAction(Crud::PAGE_NEW),

            MenuItem::section('Commercial')->setPermission('ROLE_COMMERCIAL'),
            MenuItem::linkToRoute('admin.menu.dashboard_com', 'fa fa-gauge-high', 'app')
                ->setPermission('ROLE_COMMERCIAL'),

            MenuItem::linkToCrud('Projets', 'fas fa-folder-open', Project::class)->setPermission('ROLE_COMMERCIAL'),
                MenuItem::linkToCrud('Events', 'fa fa-calendar', ProductEvent::class)->setCssClass('mx-2')->setPermission('ROLE_COMMERCIAL'),
                MenuItem::linkToCrud('Package Vip', 'fa fa-chess-king', ProductPackageVip::class)->setCssClass('mx-2')->setPermission('ROLE_COMMERCIAL'),
                MenuItem::linkToCrud('Sponsoring', 'fa fa-mug-hot', ProductSponsoring::class)->setCssClass('mx-2')->setPermission('ROLE_COMMERCIAL'),
                MenuItem::linkToCrud('Divers', 'fa fa-globe', ProductDivers::class)->setCssClass('mx-2')->setPermission('ROLE_COMMERCIAL'),
                MenuItem::linkToCrud('Archives', 'fa fa-box-archive', Project::class)->setController(ProjectArchiveCrudController::class)->setCssClass('mx-2')->setPermission('ROLE_COMMERCIAL'),

            MenuItem::linkToUrl('admin.menu.client', 'fas fa-address-book', '#'),
            MenuItem::linkToCrud('admin.menu.company', 'fas fa-address-book', Company::class)->setCssClass('mx-2')->setPermission('ROLE_COMMERCIAL'),
            MenuItem::linkToCrud('admin.menu.contact', 'fas fa-address-book', CompanyContact::class)->setCssClass('mx-2')->setPermission('ROLE_COMMERCIAL')->setController(CompanyContactCrudController::class),
            MenuItem::linkToCrud('admin.menu.email', 'fas fa-address-book', CompanyContact::class)->setCssClass('mx-2')->setPermission('ROLE_COMMERCIAL')->setController(CompanyContactMailCrudController::class),

            MenuItem::linkToCrud('admin.menu.sales', 'fas fa-comments-dollar', Sales::class)->setController(SalesCrudController::class)->setPermission('ROLE_COMMERCIAL'),
            MenuItem::linkToCrud('admin.menu.bdc', 'fas fa-receipt', SalesBdc::class)->setPermission('ROLE_COMMERCIAL'),
            MenuItem::linkToCrud('admin.menu.todo', 'fas fa-clipboard-check', Todo::class)->setPermission('ROLE_COMMERCIAL')->setBadge($this->todoRepository->countAllNoDoneTodayByUser($user), 'danger'),
            MenuItem::section('Admin')->setPermission('ROLE_ADMIN'),
            MenuItem::linkToDashboard('admin.menu.dashboard', 'fa fa-chart-line')->setPermission('ROLE_ADMIN'),
            MenuItem::linkToCrud('admin.menu.recap', 'fa fa-sliders', Sales::class)->setAction('sales_by_users_list')->setController(SalesCrudController::class)->setPermission('ROLE_ADMIN'),
            MenuItem::linkToRoute('admin.menu.recap_sales', 'fa fa-sliders', 'sales_recap')->setPermission('ROLE_ADMIN'),
            MenuItem::linkToCrud('admin.menu.users', 'fas fa-users', User::class)->setPermission('ROLE_ADMIN'),

            MenuItem::section('Gestion')->setPermission('ROLE_ENCODE'),
            MenuItem::linkToCrud('admin.menu.project', 'fas fa-folder-open', Project::class)->setPermission('ROLE_ENCODE'),
            MenuItem::linkToCrud('admin.menu.client', 'fas fa-address-book', CompanyContact::class)->setPermission('ROLE_ENCODE'),
            MenuItem::linkToCrud('admin.menu.commission', 'fas fa-hand-holding-dollar', Commission::class)->setPermission('ROLE_ENCODE'),
            MenuItem::linkToCrud('admin.menu.todo', 'fas fa-clipboard-check', Todo::class)->setPermission('ROLE_ENCODE'),
            MenuItem::linkToCrud('admin.menu.todo.category', 'fas fa-list', TodoType::class)->setCssClass('mx-2')->setPermission('ROLE_ENCODE'),

            MenuItem::section('Compta')->setPermission('ROLE_COMPTA'),
            MenuItem::linkToCrud('admin.menu.sales', 'fas fa-comments-dollar', Sales::class)->setController(ComptaCrudController::class)->setPermission('ROLE_COMPTA'),

            MenuItem::section('Import')->setPermission('ROLE_COMMERCIAL'),
            MenuItem::linkToCrud('admin.menu.import', 'fas fa-comments-dollar', Sales::class)
                ->setController(TempCompanyContactCrudController::class)
                ->setPermission('ROLE_COMMERCIAL'),

            MenuItem::section('Techniciens uniquements')->setPermission('ROLE_TECH'),
            MenuItem::linkToCrud('Css', 'fas fa-brush', Css::class)->setPermission('ROLE_TECH'),
            MenuItem::linkToRoute('Synchronise contacts', 'fa-solid fa-rotate', 'admin_sync_choice')->setPermission('ROLE_TECH'),
            MenuItem::linkToRoute('Droits', 'fas fa-right-to-bracket', 'dashboard_droits')
                ->setPermission('ROLE_TECH'),

            MenuItem::section(),
            MenuItem::linkToRoute('admin.menu.budget', 'fa-solid fa-sack-dollar', 'dashboard_budget_redirect')
                ->setPermission('ROLE_BUDGET'),
            MenuItem::linkToLogout('admin.menu.logout', 'fa-solid fa-door-open text-danger')->setCssClass('text-danger'),
        ];
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        /** @var User $user */
        $menu = [
            MenuItem::linkToRoute('admin.menu.my_profile', 'fa fa-id-card', 'admin_update_profile', []),
            MenuItem::linkToRoute('password_update.title', 'fa fa-key', 'admin_password_update', []),
        ];

        if ($user->isTotpAuthenticationEnabled()) {
            $menu[] = MenuItem::linkToRoute('2fa.disable.title', 'fa fa-unlock', 'app_2fa_disable', []);
        } else {
            $menu[] = MenuItem::linkToRoute('2fa.enable.title', 'fa fa-user-lock', 'admin_2fa_enable', []);
        }

        return parent::configureUserMenu($user)
            ->setGravatarEmail((string) $user->getUserIdentifier())
            ->addMenuItems($menu);
    }

    #[Route('/admin/{_locale}', name: 'dashboard_admin')]
    public function index(): RedirectResponse
    {
        if (!$this->isGranted('ROLE_APP')) {
            return $this->redirectToRoute('dashboard_budget');
        }

        if ($this->isGranted('ROLE_COMPTA')) {
            return $this->redirect($this->adminUrlGenerator->setController(ComptaCrudController::class)->setAction(Action::INDEX)->set('filters[invoiced]', '0')->generateUrl());
        }

        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app');
        }

        return $this->redirectToRoute('app');
    }

    #[Route('/admin/{_locale}/dashboard/com', name: 'dashboard_com')]
    public function dashboard(AdminContext $adminContext): RedirectResponse
    {
        return $this->redirectToRoute('app');
    }

    #[Route('/admin/{_locale}/dashboard/charts/my-ca', name: 'my_sales')]
    public function my_sales(AdminContext $adminContext): Response
    {
        if (!$this->isGranted('ROLE_COMMERCIAL')) {
            throw new ForbiddenActionException($adminContext);
        }

        $months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

        $default_year = (new \DateTime())->format('Y');
        $year = $this->requestStack->getCurrentRequest()->get('year', $default_year);
        /** @var User $me */
        $me = $this->getUser();
        $sales = $this->salesRepository->getStatsByMonthByUser((int) $year, $me);
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
                    'height' => '100px',
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

        return $this->render('admin/dashboard/charts/_my_ca.html.twig', [
            'chart' => $chart,
            'year' => $year,
            'locale' => $this->requestStack->getCurrentRequest()->getLocale(),
        ]);
    }

    #[Route('/admin/{_locale}/dashboard/charts/my-com', name: 'my_com')]
    public function my_com(AdminContext $adminContext): Response
    {
        if (!$this->isGranted('ROLE_COMMERCIAL')) {
            throw new ForbiddenActionException($adminContext);
        }

        $months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

        $default_year = (new \DateTime())->format('Y');
        $year = $this->requestStack->getCurrentRequest()->get('year', $default_year);
        /** @var User $me */
        $me = $this->getUser();
        $sales = $this->salesRepository->getCommissionsStatsByMonthByUser((int) $year, $me);
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

        return $this->render('admin/dashboard/charts/_my_com.html.twig', [
            'chart' => $chart,
            'year' => $year,
            'locale' => $this->requestStack->getCurrentRequest()->getLocale(),
        ]);
    }

    /**
     * Monthly CA by users.
     */
    #[Route('/admin/{_locale}/dashboard/charts/monthly_ca_by_users', name: 'users_tot')]
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
            $users_data[] = $sale['first_name'][0] . $sale['last_name'][0];
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

        return $this->render('admin/dashboard/charts/_monthly_ca_by_users.html.twig', [
            'chart2' => $chart2,
            'year' => $year,
            'month' => $months[$month - 1],
            'month_num' => $month,
            'locale' => $this->requestStack->getCurrentRequest()->getLocale(),
        ]);
    }

    #[Route('/admin/{_locale}/dashboard/charts/sales_tot', name: 'sales_tot')]
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

        return $this->render('admin/dashboard/charts/_tot_sales.html.twig', [
            'chart' => $chart,
            'year' => $year,
            'locale' => $this->requestStack->getCurrentRequest()->getLocale(),
        ]);
    }

    #[Route('{_locale}/admin/droits', name: 'dashboard_droits')]
    public function droits(AdminContext $context): Response
    {
        if (!$this->isGranted('ROLE_BOSS')) {
            throw new ForbiddenActionException($context);
        }

        $roles = $this->roleHierarchy->getReachableRoleNames(['ROLE_ADMIN']);
        $roles = array_diff($roles, ['ROLE_ALLOWED_TO_SWITCH', 'IS_AUTHENTICATED_REMEMBERED', 'IS_AUTHENTICATED_FULLY', 'ROLE_USER']);

        $finder = new Finder();
        $files = $finder->files()->in(__DIR__)->name('*CrudController*.php')->notName('*Base*');
        $params = [];
        foreach ($files as $file) {
            $params[] = 'App\\Controller\\Admin' . str_replace('.php', '', str_replace(__DIR__, '', $file->getPathname()));
        }

        if ($this->requestStack->getCurrentRequest()->get('role')) {
            $myroles = $this->requestStack->getCurrentRequest()->get('role');
        } else {
            $myroles = $this->getUser()->getRoles();
            $myroles = array_diff($myroles, ['ROLE_ALLOWED_TO_SWITCH', 'IS_AUTHENTICATED_REMEMBERED', 'IS_AUTHENTICATED_FULLY', 'ROLE_USER']);
            $myroles = implode(',', $myroles);
        }

        return $this->render('admin/voters_list.html.twig', [
            'myRoles' => $myroles,
            'roles' => $roles,
            'controllers' => $params,
        ]);
    }

    #[Route('{_locale}/admin/sync', name: 'admin_sync')]
    public function sync(KernelInterface $kernel): Response
    {
        $response = new StreamedResponse(function () use ($kernel) {
            $input = new ArrayInput([
                'command' => 'app:purge-contact',
            ]);
            $input->setInteractive(false);

            $output = new StreamedOutput(fopen('php://stdout', 'w'));

            $application = new Application($kernel);
            $application->setAutoExit(false);
            $application->run($input, $output);

            $input = new ArrayInput([
                'command' => 'app:sync-campaign-monitor-unique',
            ]);
            $input->setInteractive(false);

            $output = new StreamedOutput(fopen('php://stdout', 'w'));

            $application = new Application($kernel);
            $application->setAutoExit(false);
            $application->run($input, $output);
        });

        return $response;
    }

    #[Route('{_locale}/admin/sync/updated', name: 'admin_sync_updated')]
    public function syncUpdated(KernelInterface $kernel): Response
    {
        $response = new StreamedResponse(function () use ($kernel) {

            $input = new ArrayInput([
                'command' => 'app:purge-contact',
            ]);
            $input->setInteractive(false);

            $output = new StreamedOutput(fopen('php://stdout', 'w'));

            $application = new Application($kernel);
            $application->setAutoExit(false);
            $application->run($input, $output);

            $input = new ArrayInput([
                'command' => 'app:sync-campaign-monitor-unique-updated',
            ]);
            $input->setInteractive(false);

            $output = new StreamedOutput(fopen('php://stdout', 'w'));

            $application = new Application($kernel);
            $application->setAutoExit(false);
            $application->run($input, $output);
        });

        return $response;
    }

    #[Route('{_locale}/admin/sync_stream', name: 'admin_sync_streamed')]
    public function sync_stream(KernelInterface $kernel): Response
    {
        return $this->render('admin/sync_streamed.html.twig');
    }

    #[Route('{_locale}/admin/sync_stream_updated', name: 'admin_sync_streamed_updated')]
    public function sync_stream_updated(KernelInterface $kernel): Response
    {
        return $this->render('admin/sync_streamed_updated.html.twig');
    }

    #[Route('{_locale}/admin/sync_choice', name: 'admin_sync_choice')]
    public function sync_choice(KernelInterface $kernel): Response
    {
        return $this->render('admin/sync_choice.html.twig');
    }
}
