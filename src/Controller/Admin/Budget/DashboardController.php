<?php

declare(strict_types=1);

namespace App\Controller\Admin\Budget;

use App\Controller\Admin\Budget\Ref\CategoryCrudController;
use App\Entity\Budget\Budget;
use App\Entity\Budget\Event;
use App\Entity\Budget\Invoice;
use App\Entity\Budget\Ref\Category;
use App\Entity\Budget\Supplier;
use App\Entity\Budget\Vat;
use App\Entity\User;
use App\Repository\Budget\EventRepository;
use App\Repository\Budget\InvoiceRepository;
use App\Repository\Budget\ProductRepository;
use App\Repository\Budget\SupplierRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class DashboardController extends AbstractDashboardController
{
    public function __construct(private AdminUrlGenerator $adminUrlGenerator, private EventRepository $eventRepository, private InvoiceRepository $invoiceRepository)
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
        /** @var User $user */
        $user = $this->getUser();

        return [
            MenuItem::linkToDashboard('Dashboard', 'fa fa-home'),
            MenuItem::section('Budget'),
            MenuItem::linkToCrud('admin.menu.event', 'fas fa-calendar-days', Event::class)->setController(EventCrudController::class)->setPermission('ROLE_BUDGET'),
            MenuItem::linkToCrud('admin.menu.archived', 'fas fa-box-archive', Event::class)->setController(EventArchivedCrudController::class)->setPermission('ROLE_BUDGET'),
            MenuItem::section('Factures fournisseurs'),
            MenuItem::linkToCrud('admin.menu.invoice', 'fa-solid fa-file-invoice-dollar', Invoice::class)->setController(InvoiceCrudController::class)->setPermission('ROLE_BUDGET'),
            MenuItem::linkToCrud('admin.menu.invoiceToValide', 'fa-solid fa-file-invoice-dollar', Invoice::class)
                ->setController(InvoiceToValidCrudController::class)
                ->setPermission('ROLE_BUDGET')
                ->setBadge($this->invoiceRepository->getInvoiceToValidate($user)),
            MenuItem::section('Droits'),
            MenuItem::linkToCrud('admin.menu.users', 'fas fa-users', User::class)->setController(UserCrudController::class)->setPermission('ROLE_ADMIN_BUDGET'),
            MenuItem::section('Configurations'),
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

    #[Route('/admin/{_locale}/budget/{id}/redirect', name: 'budget_redirect')]
    public function returnToBudget(Budget $budget): RedirectResponse
    {
        $url = $this->adminUrlGenerator->setDashboard(self::class)->setController(BudgetCrudController::class)->setAction(Action::DETAIL)->setEntityId($budget->getId())->generateUrl();

        return $this->redirect($url);
    }

    #[Route('/admin/{_locale}/budget/product/save', name: 'product_save')]
    public function product_save(RequestStack $requestStack, ProductRepository $productRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $request = $requestStack->getCurrentRequest();
        $data = json_decode($request->getContent());
        $datas = $data->data;

        $product = $productRepository->find($datas->id);
        $product->setTitle($datas->title);
        $product->setQuantity((int)$datas->qty);
        $product->setPrice($datas->price);
        $product->setOfferPrice((string) $datas->offerPrice);
        $product->setRealPrice($datas->realPrice);

        $entityManager->persist($product);
        $entityManager->flush();
        return new JsonResponse($datas);
    }

    #[Route('/admin/{_locale}/budget/supplier/get', name: 'supplier_get')]
    public function get_supplier(SupplierRepository $supplierRepository): JsonResponse
    {
        $datas = $supplierRepository->findAll();
        $return = [];
        foreach ($datas as $key => $data) {
            $return[$key] = [
                'id' => $data->getId(),
                'name' => $data->getName(),
            ];
        }
        return new JsonResponse($return);
    }

    #[Route('/admin/{_locale}/budget/supplier/save', name: 'supplier_save')]
    public function save_supplier(RequestStack $requestStack, EntityManagerInterface $entityManager): JsonResponse
    {
        $request = $requestStack->getCurrentRequest();
        $data = json_decode($request->getContent());
        $supplier = new Supplier();
        $supplier->setName($data);

        $entityManager->persist($supplier);
        $entityManager->flush();
        return new JsonResponse(['id' => $supplier->getId(), 'name' => $supplier->getName()]);
    }
}
