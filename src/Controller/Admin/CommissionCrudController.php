<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Commission;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\PercentField;

class CommissionCrudController extends BaseCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        $crud->setEntityLabelInPlural('Commissions')
            ->setEntityLabelInSingular('Commission')
            ->showEntityActionsInlined(true);

        return parent::configureCrud($crud);
    }

    public static function getEntityFqcn(): string
    {
        return Commission::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $percentCom = PercentField::new('percent_com')->setRequired(true)->setStoredAsFractional(false);
        $product = AssociationField::new('product')->setRequired(true);
        $user = AssociationField::new('user')->setRequired(true);

        $response = [$product, $user, $percentCom];

        return $response;
    }
}