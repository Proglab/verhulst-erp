<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Sales;
use App\Repository\SalesRepository;
use App\Service\SecurityChecker;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ComptaCrudController extends BaseCrudController
{
    public function __construct(private SalesRepository $salesRepository, private AdminUrlGenerator $adminUrlGenerator, protected SecurityChecker $securityChecker)
    {
        parent::__construct($securityChecker);
    }

    public static function getEntityFqcn(): string
    {
        return Sales::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud->setEntityLabelInPlural('Ventes')
            ->setEntityLabelInSingular('Vente')
            ->showEntityActionsInlined(true)
            ->setEntityPermission('ROLE_COMPTA')
            ->setDefaultSort(['invoiced' => 'ASC'])
            ->overrideTemplate('crud/detail', 'admin/crud/detail_2cols.html.twig');

        return parent::configureCrud($crud);
    }

    public function configureFields(string $pageName): iterable
    {
        $panelProduct = FormField::addPanel('Produit')->setCustomOption('cols', 1);

        $panelClient = FormField::addPanel('Client')->setCustomOption('cols', 2);

        $panelVente = FormField::addPanel('Vente')->setCustomOption('cols', 1);

        $panelContact = FormField::addPanel('Contact')->setCustomOption('cols', 2);

        $price = MoneyField::new('price')
            ->setStoredAsCents(false)
            ->setNumDecimals(2)
            ->setRequired(true)
            ->setCurrency('EUR')->setLabel('Prix Unitaire');

        $priceTotal = MoneyField::new('total_price')
            ->setStoredAsCents(false)
            ->setNumDecimals(2)
            ->setRequired(true)
            ->setCurrency('EUR')->setLabel('Prix Total');

        $priceMarge = MoneyField::new('marge')
            ->setStoredAsCents(false)
            ->setNumDecimals(2)
            ->setRequired(true)
            ->setCurrency('EUR')->setLabel('Prix final');

        $project = TextField::new('product.project')->setLabel('Projet');
        $product = TextField::new('product')->setLabel('Product');

        $dateBegin = DateField::new('product.date_begin')->setLabel('Du')->setFormat('dd/MM/yy');
        $dateEnd = DateField::new('product.date_end')->setLabel('Au')->setFormat('dd/MM/yy');

        $description = TextField::new('product.description')->setLabel('Description')->renderAsHtml();

        $user = TextField::new('user.fullname')->setLabel('Vendeur');
        $userMail = EmailField::new('user.email')->setLabel('Mail');

        $company = TextField::new('contact.company')->setLabel('Société');
        $companyVat = TextField::new('contact.company.vat_number')->setLabel('Tva');
        $companyStreet = TextField::new('contact.company.street')->setLabel('Rue');
        $companyPc = TextField::new('contact.company.pc')->setLabel('Code postal');
        $companyCity = TextField::new('contact.company.city')->setLabel('Ville');
        $companyCountry = CountryField::new('contact.company.country')->setLabel('Pays');
        $date = DateField::new('date')->setLabel('Date de vente')->setFormat('dd/MM/yy');
        $contact = TextField::new('contact.fullname')->setLabel('Nom');
        $contactTel = TelephoneField::new('contact.phone')->setLabel('Tel');
        $contactGsm = TelephoneField::new('contact.gsm')->setLabel('Gsm');
        $contactEmail = EmailField::new('contact.email')->setLabel('Mail');
        $quantity = IntegerField::new('quantity')->setLabel('Quantité');
        $discount = MoneyField::new('discount')
            ->setStoredAsCents(false)
            ->setNumDecimals(2)
            ->setRequired(false)
            ->setCurrency('EUR')
            ->setLabel('Réduction (EUR)');
        $total = MoneyField::new('marge')
            ->setStoredAsCents(false)
            ->setNumDecimals(2)
            ->setRequired(false)
            ->setCurrency('EUR')
            ->setLabel('Prix total (EUR)');
        $invoiced = BooleanField::new('invoiced')
            ->setLabel('Facturé')->setDisabled(true);

        if ($this->isGranted('ROLE_ADMIN')) {
            $invoiced->setDisabled(false);
        }
        $dateValidation = DateField::new('invoicedDt')->setLabel('Date de validation')->setFormat('dd/MM/yy HH:MM');

        switch ($pageName) {
            case Crud::PAGE_INDEX:
                $response = [$company, $project, $product, $price, $quantity, $discount, $total, $date, $invoiced, $dateValidation];
                break;

            case Crud::PAGE_DETAIL:
                $response = [$panelProduct,
                    $project, $product, $dateBegin, $dateEnd, $user, $userMail, $description,
                    $panelClient,
                    $company, $companyVat, $companyStreet, $companyPc, $companyCity, $companyCountry,
                    $panelVente,
                    $price, $quantity, $priceTotal, $discount, $priceMarge, $date, $invoiced, $dateValidation,
                    $panelContact,
                    $contact, $contactTel, $contactGsm, $contactEmail];
                break;
            case Crud::PAGE_EDIT:
                $response = [$invoiced];
                break;
            default:
                $response = [$project, $product, $quantity, $price, $date, $discount];
        }

        return $response;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);
        $granted = $this->isGranted('ROLE_ADMIN');

        $setInvoiced = Action::new('setInvoiced', false)
            ->displayIf(static function ($entity) use ($granted) {
                return !$entity->isInvoiced() || $granted;
            })
            ->linkToCrudAction('setInvoiced');

        $actions
            ->disable(Action::NEW)
            ->disable(Action::DELETE)
            ->disable(Action::SAVE_AND_CONTINUE)
            ->disable(Action::SAVE_AND_RETURN)
            ->disable(Action::SAVE_AND_ADD_ANOTHER)
            ->setPermission(Action::EDIT, 'ROLE_ADMIN')
            ->setPermission(Action::DETAIL, 'ROLE_COMPTA')
            ->setPermission(Action::INDEX, 'ROLE_COMPTA')
            ->setPermission('setInvoiced', 'ROLE_COMPTA')
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $setInvoiced)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fa fa-eye')->setLabel(false);
            })
            ->update(Crud::PAGE_DETAIL, 'setInvoiced', function (Action $action) {
                return $action->setIcon('fa fa-check')->setLabel('Vente encodée')->addCssClass('btn btn-success');
            })
        ;

        return $actions;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('date')
            ->add('invoiced')
            ->add('invoiced_dt')
        ;
    }

    public function setInvoiced(AdminContext $adminContext): RedirectResponse
    {
        /** @var Sales $sale */
        $sale = $adminContext->getEntity()->getInstance();
        $sale->setInvoiced(true);
        if (null === $sale->getInvoicedDt()) {
            $sale->setInvoicedDt(new \DateTime());
        }
        $this->salesRepository->save($sale, true);
        $next = $this->salesRepository->findOneBy(['invoiced' => false]);
        if (null === $next) {
            return $this->redirect($this->adminUrlGenerator->setAction(Action::INDEX)->setEntityId(null)->generateUrl());
        }

        return $this->redirect($this->adminUrlGenerator->setAction(Action::DETAIL)->setEntityId($next->getId())->generateUrl());
    }
}
