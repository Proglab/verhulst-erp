<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ProductPackageVip;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\PercentField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProductPackageVipCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProductPackageVip::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $name = TextField::new('name');
        $percentVr = PercentField::new('percent_vr')->setLabel('Com Verhulst')->setPermission('ROLE_ADMIN')->setStoredAsFractional(false);
        $ca = MoneyField::new('ca')->setCurrency('EUR')->setStoredAsCents(false)->setLabel('Prix de vente');
        $description = TextEditorField::new('description');
        $mail = BooleanField::new('mail')->setLabel('Prévenir les commerciaux ?');

        switch ($pageName) {
            case Crud::PAGE_DETAIL:
            case Crud::PAGE_INDEX:
                $response = [$name, $percentVr, $ca, $description];
                break;
            case Crud::PAGE_NEW:
                $response = [$name, $percentVr, $ca, $description, $mail];
                break;
            case Crud::PAGE_EDIT:
                $response = [$name, $percentVr, $ca, $description];
                break;
            default:
                $response = [$name, $percentVr, $ca, $description];
        }

        return $response;
    }
}
