<?php

declare(strict_types=1);

namespace App\Controller\Admin\Budget;

use App\Entity\Budget\Vat;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\PercentField;

class VatCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return Vat::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInPlural('TVA')
            ->setEntityLabelInSingular('TVA')
            ->showEntityActionsInlined(true);

        return $crud;
    }

    public function configureFields(string $pageName): iterable
    {
        $percent = PercentField::new('percent')->setLabel('Pourcentage')->setNumDecimals(2)->setStoredAsFractional(false);

        return match ($pageName) {
            default => [$percent],
        };
    }
}
