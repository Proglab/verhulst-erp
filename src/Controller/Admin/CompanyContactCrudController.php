<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\CompanyContact;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\LanguageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class CompanyContactCrudController extends BaseCrudController
{
    public function __construct(private AdminUrlGenerator $adminUrlGenerator)
    {
    }

    public static function getEntityFqcn(): string
    {
        return CompanyContact::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud->setEntityLabelInPlural('Clients')
            ->setEntityLabelInSingular('Client')
            ->showEntityActionsInlined(true)
            ->overrideTemplate('crud/detail', 'admin/crud/detail_2cols.html.twig');

        return parent::configureCrud($crud);
    }

    public function configureFields(string $pageName): iterable
    {
        $panel1 = FormField::addPanel('Société')->setCustomOption('cols', 1);
        $panel2 = FormField::addPanel('Contact')->setCustomOption('cols', 2);
        $company = TextField::new('company')->setRequired(true)->setLabel('Société');
        $companyStreet = TextField::new('company.street')->setLabel('Rue')->setColumns(12);
        $companyPc = TextField::new('company.pc')->setLabel('Code postal');
        $companyCity = TextField::new('company.city')->setLabel('Ville')->setColumns(12);
        $companyCountry = CountryField::new('company.country')->setLabel('Pays');
        $companyVat = TextField::new('company.vat_number')->setLabel('Numéro de TVA');
        $firstname = TextField::new('firstname')->setLabel('Prénom')->setRequired(true)->setColumns(12);
        $lastname = TextField::new('lastname')->setLabel('Nom')->setRequired(true)->setColumns(12);
        $fullname = TextField::new('fullName')->setLabel('Nom');
        $lang = LanguageField::new('lang')->setLabel('Langue')->setRequired(true)->setColumns(12)->includeOnly(['fr', 'nl', 'en']);
        $langListing = LanguageField::new('lang')->setLabel('Lang')->showCode()->showName(false)->setRequired(true)->setColumns(12);
        $email = EmailField::new('email')->setLabel('E-mail')->setColumns(12);
        $phone = TelephoneField::new('phone')->setLabel('Téléphone')->setColumns(12);
        $gsm = TextField::new('gsm')->setLabel('Gsm')->setColumns(12);
        $note = TextEditorField::new('note')->setLabel('Note')->setColumns(12);
        $noteView = TextField::new('note')->setLabel('Note')->renderAsHtml();

        $panel3 = FormField::addPanel('Facturation')->setCustomOption('cols', 1);

        $billingstreet = TextField::new('company.billing_street')->setRequired(true)->setColumns(12)->setLabel('Rue');
        $billingPc = TextField::new('company.billing_pc')->setRequired(true)->setColumns(12)->setLabel('Code postal');
        $billingcity = TextField::new('company.billing_city')->setRequired(true)->setColumns(12)->setLabel('Ville');
        $billingcountry = CountryField::new('company.billing_country')->setRequired(true)->setLabel('Pays');
        $billingmail = EmailField::new('company.billing_mail')->setRequired(true)->setLabel('Email');

        $user = AssociationField::new('added_by')->setRequired(false)->setFormTypeOption('query_builder', function (UserRepository $entityRepository) {
            return $entityRepository->getCommercialsQb();
        })->setLabel('Commercial')->setValue($this->getUser());

        $userName = TextField::new('added_by.fullName')->setLabel('Commercial');
        $userNameListing = TextField::new('added_by.fullNameMinified')->setLabel('Sales');

        switch ($pageName) {
            case Crud::PAGE_EDIT:
            case Crud::PAGE_NEW:
                $response = [$firstname, $lastname, $lang, $email, $phone, $gsm, $note, $user];
                break;
            case Crud::PAGE_DETAIL:
                $response = [$panel1, $company, $companyVat, $companyStreet, $companyPc, $companyCity, $companyCountry, $panel2, $fullname, $lang, $email, $phone, $gsm, $userName, $noteView, $panel3, $billingstreet, $billingPc, $billingcity, $billingcountry, $billingmail];
                break;
            case Crud::PAGE_INDEX:
                $response = [$company, $fullname, $langListing, $email, $phone, $gsm, $userNameListing, $note];
                break;
            default:
                $response = [$company, $firstname, $lastname, $lang, $email, $phone, $note];
        }

        return $response;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);
        $actions
            ->setPermission(Action::NEW, 'ROLE_COMMERCIAL')
            ->setPermission(Action::EDIT, 'ROLE_COMMERCIAL')
            ->setPermission(Action::DELETE, 'ROLE_COMMERCIAL')
            ->setPermission(Action::DETAIL, 'ROLE_COMMERCIAL')
            ->setPermission(Action::INDEX, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_RETURN, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_ADD_ANOTHER, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_CONTINUE, 'ROLE_COMMERCIAL')
            ->remove(crud::PAGE_DETAIL, Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fa fa-eye')->setLabel(false)->setHtmlAttributes(['title' => 'Consulter']);
            })
        ;

        return $actions;
    }

    /**
     * @return RedirectResponse|KeyValueStore|Response
     */
    public function new(AdminContext $context)
    {
        $url = $this->adminUrlGenerator
            ->setController(CompanyCrudController::class)
            ->setAction(Action::NEW)
            ->generateUrl();

        return $this->redirect($url);
    }

    /**
     * @return RedirectResponse|KeyValueStore|Response
     */
    public function edit(AdminContext $context)
    {
        /** @var CompanyContact $contact */
        $contact = $context->getEntity()->getInstance();
        $url = $this->adminUrlGenerator
            ->setController(CompanyCrudController::class)
            ->setAction(Action::EDIT)
            ->setEntityId($contact->getCompany()->getId())
            ->generateUrl();

        return $this->redirect($url);
    }
}
