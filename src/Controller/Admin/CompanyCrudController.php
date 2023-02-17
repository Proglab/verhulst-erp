<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Company;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class CompanyCrudController extends BaseCrudController
{
    public function __construct(private AdminUrlGenerator $adminUrlGenerator)
    {
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud->setEntityLabelInPlural('Clients')
            ->setEntityLabelInSingular('Client')
        ->showEntityActionsInlined(true);

        return parent::configureCrud($crud);
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

    public static function getEntityFqcn(): string
    {
        return Company::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $panel1 = FormField::addPanel()->addCssClass('col-6');
        $name = TextField::new('name')->setRequired(true)->setColumns(12)->setLabel('Nom de la société');
        $street = TextField::new('street')->setColumns(12)->setLabel('Rue');
        $number = TextField::new('number')->setLabel('Numéro');
        $box = TextField::new('box')->setLabel('Boîte');
        $pc = TextField::new('pc')->setLabel('Code postal');
        $city = TextField::new('city')->setColumns(12)->setLabel('Ville');
        $country = CountryField::new('country')->setLabel('Pays');
        $vat = TextField::new('vat_number')->setLabel('Numéro de TVA');
        $panel2 = FormField::addPanel()->addCssClass('col-6');
        $contacts = CollectionField::new('contact')->setLabel('Contacts')->allowAdd(true)->allowDelete(true)->useEntryCrudForm(CompanyContactCrudController::class)->setColumns(12);
        $note = TextEditorField::new('note')->setLabel('Note');

        $response = [$panel1, $name, $street, $number, $box, $pc, $city, $country, $vat, $note, $panel2, $contacts];

        return $response;
    }

    public function index(AdminContext $context)
    {
        $url = $this->adminUrlGenerator
            ->setController(CompanyContactCrudController::class)
            ->setAction(Action::NEW)
            ->generateUrl();

        return $this->redirect($url);
    }
}
