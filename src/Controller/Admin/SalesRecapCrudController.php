<?php

namespace App\Controller\Admin;

use App\Entity\Sales;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class SalesRecapCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return Sales::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud->setEntityLabelInPlural('Récapitulatif des Ventes')
            ->setEntityLabelInSingular('Vente')
            ->showEntityActionsInlined(true)
            ->setEntityPermission('ROLE_ADMIN');

        return parent::configureCrud($crud);
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);
        $actions
            ->disable(Action::NEW)
            ->disable(Action::EDIT)
            ->disable(Action::DELETE)
            ->disable(Action::SAVE_AND_RETURN)
            ->disable(Action::SAVE_AND_ADD_ANOTHER)
            ->disable(Action::SAVE_AND_CONTINUE)
        ;

        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {

        $project = TextField::new('product.project');
        $product = AssociationField::new('product');
        $company = TextField::new('contact.company');
        $sales = TextField::new('user');
        $quantity = IntegerField::new('quantity');

        $price = MoneyField::new('getTotalPrice')
            ->setStoredAsCents(false)
            ->setNumDecimals(2)
            ->setRequired(true)
            ->setCurrency('EUR')->setLabel('Prix final');

        $comSales = MoneyField::new('getEuroCom')
            ->setStoredAsCents(false)
            ->setNumDecimals(2)
            ->setRequired(true)
            ->setCurrency('EUR')->setLabel('Com sales');
        $margeVr = MoneyField::new('getDiffCa')
            ->setStoredAsCents(false)
            ->setNumDecimals(2)
            ->setRequired(true)
            ->setCurrency('EUR')->setLabel('Marge Vr');

        $invoiced = BooleanField::new('isInvoiced')->setLabel('Facturé')->setDisabled(true);

        return [$project, $product, $company, $sales, $quantity, $price, $comSales, $margeVr, $invoiced];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('product')
            ->add('user')
            ->add(EntityFilter::new('contact'))
            ;
    }
}
