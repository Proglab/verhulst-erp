<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ProductSponsoring;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\PercentField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProductSponsoringCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProductSponsoring::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $name = TextField::new('name');
        $percentVr = PercentField::new('percent_vr')->setLabel('Com Verhulst')->setPermission('ROLE_ADMIN')->setStoredAsFractional(false);
        $ca = MoneyField::new('ca')->setCurrency('EUR')->setStoredAsCents(false)->setLabel('Prix de vente');
        $description = TextEditorField::new('description');
        $quantityMax = IntegerField::new('quantity_max');

        switch ($pageName) {
            case Crud::PAGE_DETAIL:
            case Crud::PAGE_INDEX:
            case Crud::PAGE_EDIT:
                $response = [$name, $percentVr, $ca, $description, $quantityMax];
                break;
            case Crud::PAGE_NEW:
                $response = [$name, $percentVr, $ca, $description, $quantityMax];
                break;
            default:
                $response = [$name, $percentVr, $ca, $description, $quantityMax];
        }

        return $response;
    }
}
