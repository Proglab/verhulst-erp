<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\CompanyContact;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

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
            ->showEntityActionsInlined(true);

        return parent::configureCrud($crud);
    }

    public function configureFields(string $pageName): iterable
    {
        $company = AssociationField::new('company')->setRequired(true);

        $panel1 = FormField::addPanel()->addCssClass('col-6');

        $companyName = TextField::new('company.name')->setLabel('Société')->setRequired(true)->setColumns(12);
        $companyStreet = TextField::new('company.street')->setLabel('Rue')->setColumns(12);
        $companyNumber = TextField::new('company.number')->setLabel('Numéro');
        $companyBox = TextField::new('company.box')->setLabel('Boîte');
        $companyPc = TextField::new('company.pc')->setLabel('Code postal');
        $companyCity = TextField::new('company.city')->setLabel('Ville')->setColumns(12);
        $companyCountry = CountryField::new('company.country')->setLabel('Pays');
        $companyVat = TextField::new('company.vat_number')->setLabel('Numéro de TVA');

        $panel2 = FormField::addPanel()->addCssClass('col-6');

        $firstname = TextField::new('firstname')->setLabel('Prénom')->setRequired(true)->setColumns(12);
        $lastname = TextField::new('lastname')->setLabel('Nom')->setRequired(true)->setColumns(12);
        $lang = ChoiceField::new('lang')->setLabel('Langue')->allowMultipleChoices(false)->renderExpanded(false)->setChoices(['Français' => 'fr', 'Nederlands' => 'nl', 'English' => 'en'])->setRequired(true)->setColumns(12);
        $email = EmailField::new('email')->setLabel('E-mail')->setColumns(12);
        $phone = TextField::new('phone')->setLabel('Téléphone')->setColumns(12);

        $user = AssociationField::new('added_by')->setRequired(true)->setFormTypeOption('query_builder', function (UserRepository $entityRepository) {
            return $entityRepository->getCommercialsQb();
        });
        $userName = TextField::new('added_by.fullname')->setLabel('Nom')->setRequired(true)->setColumns(12);

        switch ($pageName) {
            case Crud::PAGE_NEW:
                $response = [$firstname, $lastname, $lang, $email, $phone, $user];
                break;
            case Crud::PAGE_DETAIL:
            case Crud::PAGE_INDEX:
                $response = [$company, $firstname, $lastname, $lang, $email, $phone, $user];
                break;
            case Crud::PAGE_EDIT:
                $response = [$panel1, $companyName, $companyStreet, $companyNumber, $companyBox, $companyPc, $companyCity, $companyCountry, $companyVat, $panel2, $firstname, $lastname, $email, $phone, $user];
                break;
            default:
                $response = [$company, $firstname, $lastname, $lang, $email, $phone];
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
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fa fa-eye');
            })
        ;

        return $actions;
    }

    public function new(AdminContext $context)
    {
        $url = $this->adminUrlGenerator
            ->setController(CompanyCrudController::class)
            ->setAction(Action::NEW)
            ->generateUrl();

        return $this->redirect($url);
    }
}
