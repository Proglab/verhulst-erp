<?php

namespace App\Controller\Admin\Budget;

use App\Entity\Budget\Supplier;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\PercentField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SupplierCrudController extends BaseCrudController
{

    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInPlural('Fournisseurs')
            ->setEntityLabelInSingular('Fournisseur')
            ->showEntityActionsInlined(true);

        return $crud;
    }

    public static function getEntityFqcn(): string
    {
        return Supplier::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $percent = TextField::new('name')->setLabel('Nom du fournisseur');

        return match ($pageName) {
            default => [$percent],
        };
    }
}
