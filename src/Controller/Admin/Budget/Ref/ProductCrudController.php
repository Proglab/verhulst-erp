<?php

declare(strict_types=1);

namespace App\Controller\Admin\Budget\Ref;

use App\Entity\Budget\Ref\Product;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud->setEntityLabelInPlural('Produits')
            ->setEntityLabelInSingular('Produit')
            ->showEntityActionsInlined(true);

        return parent::configureCrud($crud);
    }

    public function configureFields(string $pageName): iterable
    {
        $name = TextField::new('title')->setLabel('Titre');
        $description = TextareaField::new('description')->setLabel('Description');
        $tva = AssociationField::new('vat', 'Tva');

        return [$name, $description, $tva];
    }
}
