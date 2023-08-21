<?php

namespace App\Controller\Admin\Budget\Ref;

use App\Entity\Budget\Ref\SubCategory;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SubCategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SubCategory::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud->setEntityLabelInPlural('Sous Catégories générales')
            ->setEntityLabelInSingular('Sous Catégorie générale')
            ->showEntityActionsInlined(true);

        return parent::configureCrud($crud);
    }

    public function configureFields(string $pageName): iterable
    {
        $name = TextField::new('name')->setLabel('Nom');
        return [$name];
    }
}
