<?php

declare(strict_types=1);

namespace App\Controller\Admin\Budget;

use App\Controller\Admin\Budget\Ref\CategoryCrudController;
use App\Entity\Budget\Event;
use App\Entity\Budget\Ref\Category;
use App\Entity\Budget\Supplier;
use App\Entity\Budget\Vat;
use App\Entity\User;
use App\Repository\Budget\EventRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class DashboardController extends AbstractDashboardController
{
    public function __construct(private AdminUrlGenerator $adminUrlGenerator, private EventRepository $eventRepository)
    {
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

    public function configureAssets(): Assets
    {
        $assets = parent::configureAssets();

        return $assets->addWebpackEncoreEntry('htmx');
    }

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::linkToDashboard('Dashboard', 'fa fa-home'),
            MenuItem::section("Budget"),
            MenuItem::linkToCrud('admin.menu.event', 'fas fa-calendar-days', Event::class)->setController(EventCrudController::class)->setPermission('ROLE_BUDGET'),
            MenuItem::linkToCrud('admin.menu.archived', 'fas fa-box-archive', Event::class)->setController(EventArchivedCrudController::class)->setPermission('ROLE_BUDGET'),
            MenuItem::section("Droits"),
            MenuItem::linkToCrud('admin.menu.users', 'fas fa-users', User::class)->setController(UserCrudController::class)->setPermission('ROLE_ADMIN_BUDGET'),
            MenuItem::section("Configurations"),
            MenuItem::linkToCrud('admin.menu.category_ref', 'fas fa-tags', Category::class)->setController(CategoryCrudController::class)->setPermission('ROLE_ADMIN_BUDGET'),
            MenuItem::linkToCrud('admin.menu.tva', 'fas fa-building-columns', Vat::class)->setController(VatCrudController::class)->setPermission('ROLE_ADMIN_BUDGET'),
            MenuItem::linkToCrud('admin.menu.supplier', 'fas fa-truck-field', Supplier::class)->setController(SupplierCrudController::class)->setPermission('ROLE_BUDGET'),
            MenuItem::section(),
            MenuItem::linkToUrl('admin.menu.app', 'fas fa-mobile-screen', '/admin/fr/')->setPermission('ROLE_APP'),
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

    #[Route('/admin/{_locale}/budget', name: 'dashboard_budget')]
    public function index(): Response
    {
        $events = $this->eventRepository->getNonArchivedEvents();
        return $this->render('admin/budget/dashboard.html.twig', ['events' => $events]);
    }

    #[Route('/admin/{_locale}/budget/redirect', name: 'dashboard_budget_redirect')]
    public function redirection(): Response
    {
        return $this->redirect($this->adminUrlGenerator->setDashboard(self::class)->setRoute('dashboard_budget')->generateUrl());
    }
}
