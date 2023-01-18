<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ProductEvent;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
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
        $percentVr = PercentField::new('percent_vr')->setLabel('Com Verhulst')->setPermission('ROLE_ADMIN');

        switch ($pageName) {
            case Crud::PAGE_DETAIL:
            case Crud::PAGE_INDEX:
                $response = [$name, $date, $percentVr];
                break;
            case Crud::PAGE_NEW:
            case Crud::PAGE_EDIT:
                $response = [$name, $date, $percentVr];
                break;
            default:
                $response = [$name, $date, $percentVr];
        }

        return $response;
    }
}
