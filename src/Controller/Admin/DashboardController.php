<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Commission;
use App\Entity\CompanyContact;
use App\Entity\Css;
use App\Entity\ProductDivers;
use App\Entity\ProductEvent;
use App\Entity\ProductPackageVip;
use App\Entity\ProductSponsoring;
use App\Entity\Project;
use App\Entity\Sales;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function admin(): Response
    {
        return $this->redirectToRoute('dashboard_admin');
    }

    #[Route('/admin', name: 'app_admin_dashboard_index')]
    public function admin2(): Response
    {
        return $this->redirectToRoute('dashboard_admin');
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
        return [
            MenuItem::section('Commercial')->setPermission('ROLE_COMMERCIAL'),
            MenuItem::linkToRoute('admin.menu.dashboard_com', 'fa fa-gauge-high', 'dashboard_com')->setPermission('ROLE_COMMERCIAL'),

            MenuItem::linkToCrud('Projets', 'fas fa-folder-open', Project::class)->setPermission('ROLE_COMMERCIAL'),
                MenuItem::linkToCrud('Events', 'fa fa-calendar', ProductEvent::class)->setCssClass('mx-2')->setPermission('ROLE_COMMERCIAL'),
                MenuItem::linkToCrud('Package Vip', 'fa fa-chess-king', ProductPackageVip::class)->setCssClass('mx-2')->setPermission('ROLE_COMMERCIAL'),
                MenuItem::linkToCrud('Sponsoring', 'fa fa-mug-hot', ProductSponsoring::class)->setCssClass('mx-2')->setPermission('ROLE_COMMERCIAL'),
                MenuItem::linkToCrud('Divers', 'fa fa-globe', ProductDivers::class)->setCssClass('mx-2')->setPermission('ROLE_COMMERCIAL'),

            MenuItem::linkToCrud('admin.menu.client', 'fas fa-address-book', CompanyContact::class)->setPermission('ROLE_COMMERCIAL'),
            MenuItem::linkToCrud('admin.menu.sales', 'fas fa-comments-dollar', Sales::class)->setPermission('ROLE_COMMERCIAL'),

            MenuItem::section('Admin')->setPermission('ROLE_ADMIN'),
            MenuItem::linkToDashboard('admin.menu.dashboard', 'fa fa-chart-line')->setPermission('ROLE_ADMIN'),
            MenuItem::linkToCrud('admin.menu.recap', 'fa fa-sliders', Sales::class)->setAction('sales_by_users_list')->setPermission('ROLE_ADMIN'),
            MenuItem::linkToCrud('admin.menu.users', 'fas fa-users', User::class)->setPermission('ROLE_ADMIN'),

            MenuItem::section('Gestion')->setPermission('ROLE_ENCODE'),
            MenuItem::linkToCrud('admin.menu.project', 'fas fa-folder-open', Project::class)->setPermission('ROLE_ENCODE'),
            MenuItem::linkToCrud('admin.menu.client', 'fas fa-address-book', CompanyContact::class)->setPermission('ROLE_ENCODE'),
            MenuItem::linkToCrud('admin.menu.commission', 'fas fa-hand-holding-dollar', Commission::class)->setPermission('ROLE_ENCODE'),

            MenuItem::section('Compta')->setPermission('ROLE_COMPTA'),
            MenuItem::linkToCrud('admin.menu.sales', 'fas fa-comments-dollar', Sales::class)->setController(ComptaCrudController::class)->setPermission('ROLE_COMPTA'),

            MenuItem::section('Techniciens uniquements')->setPermission('ROLE_TECH'),
            MenuItem::linkToCrud('Css', 'fas fa-brush', Css::class)->setPermission('ROLE_TECH'),

            MenuItem::section(),
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
}
