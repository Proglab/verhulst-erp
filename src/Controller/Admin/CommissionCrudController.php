<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Commission;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

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
}
