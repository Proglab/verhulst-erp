<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Company;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CompanyCrudController extends BaseCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        $crud->setEntityLabelInPlural('Clients')
            ->setEntityLabelInSingular('Client')
        ->showEntityActionsInlined(true);

        return parent::configureCrud($crud);
    }

    public static function getEntityFqcn(): string
    {
        return Company::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $panel1 = FormField::addPanel()->addCssClass('col-6');
        $name = TextField::new('name')->setRequired(true)->setColumns(12);
        $street = TextField::new('street')->setColumns(12);
        $number = TextField::new('number');
        $box = TextField::new('box');
        $pc = TextField::new('pc');
        $city = TextField::new('city')->setColumns(12);
        $country = TextField::new('country');
        $vat = TextField::new('vat_number');
        $panel2 = FormField::addPanel()->addCssClass('col-6');
        $contacts = CollectionField::new('contact')->setLabel('Contacts')->allowAdd(true)->allowDelete(true)->useEntryCrudForm(CompanyContactCrudController::class)->setColumns(12);

        $response = [$panel1, $name, $street, $number, $box, $pc, $city, $country, $vat, $panel2, $contacts];

        return $response;
    }
}
