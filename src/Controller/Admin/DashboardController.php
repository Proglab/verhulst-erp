<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Commission;
use App\Entity\Company;
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
    public function configureAssets(): Assets
    {
        $assets = parent::configureAssets();
        return $assets->addWebpackEncoreEntry('htmx');
    }

    #[Route('/{_locale}/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Verhulst ERP')
            ->renderContentMaximized()
            ->setLocales([
                'fr' => 'Français',
                'nl' => 'Neederlands',
            ]);
    }

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::linkToDashboard('admin.menu.dashboard', 'fa fa-home'),

            MenuItem::linkToCrud('admin.menu.users', 'fas fa-users', User::class)->setPermission('ROLE_ADMIN'),
            MenuItem::linkToCrud('admin.menu.project', 'fas fa-folder-open', Project::class)->setPermission('ROLE_COMMERCIAL'),
            MenuItem::linkToCrud('admin.menu.client', 'fas fa-address-book', Company::class)->setPermission('ROLE_COMMERCIAL'),
            MenuItem::linkToCrud('admin.menu.commission', 'fas fa-hand-holding-dollar', Commission::class)->setPermission('ROLE_ADMIN'),
            MenuItem::linkToCrud('admin.menu.sales', 'fas fa-comments-dollar', Sales::class)->setPermission('ROLE_COMMERCIAL'),

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
