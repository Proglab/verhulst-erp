<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ProductDivers;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\PercentField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProductDiversCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProductDivers::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $name = TextField::new('name');
        $percentVr = PercentField::new('percent_vr')->setLabel('Com Verhulst')->setPermission('ROLE_ADMIN')->setStoredAsFractional(false);

        switch ($pageName) {
            case Crud::PAGE_DETAIL:
            case Crud::PAGE_INDEX:
                $response = [$name, $percentVr];
                break;
            case Crud::PAGE_NEW:
            case Crud::PAGE_EDIT:
                $response = [$name, $percentVr];
                break;
            default:
                $response = [$name, $percentVr];
        }

        return $response;
    }
}
