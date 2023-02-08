<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ProductEvent;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\PercentField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProductEventCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProductEvent::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $name = TextField::new('name');
        $date = DateField::new('date');
        $percentVr = PercentField::new('percent_vr')
            ->setLabel('Com Verhulst')
            ->setPermission('ROLE_ADMIN')
            ->setNumDecimals(2)
            ->setStoredAsFractional(true);

        $pa = MoneyField::new('pa')
            ->setStoredAsCents()
            ->setNumDecimals(2)
            ->setLabel('Prix achat')
            ->setCurrency('EUR');

        $image = ImageField::new('doc')->setBasePath('files/products')->setUploadDir('../../shared/public/files/products');

        switch ($pageName) {
            case Crud::PAGE_DETAIL:
            case Crud::PAGE_INDEX:
                $response = [$name, $date, $percentVr, $pa];
                break;
            case Crud::PAGE_NEW:
            case Crud::PAGE_EDIT:
                $response = [$name, $date, $percentVr, $pa, $image];
                break;
            default:
                $response = [$name, $date, $percentVr, $pa];
        }

        return $response;
    }
}
