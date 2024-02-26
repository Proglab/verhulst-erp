<?php

namespace App\Controller\Admin\Budget;

use App\Admin\Field\FileField;
use App\Entity\Budget\Invoice;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;

class InvoiceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Invoice::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud->setEntityLabelInPlural('Factures fournisseurs')
            ->setEntityLabelInSingular('Facture fournisseur')
            ->showEntityActionsInlined(true)
            ->setEntityPermission('ROLE_BUDGET');

        return parent::configureCrud($crud);
    }

    public function configureFields(string $pageName): iterable
    {
        $return = [
            FileField::new('doc', 'Facture au format PDF')->setRequired(true),
            AssociationField::new('event', 'Evènement')->setQueryBuilder(
                fn (QueryBuilder $queryBuilder) => $queryBuilder
                    ->andWhere('entity.archived = :archived')
                    ->setParameter('archived', false)
            )->setRequired(false),
            AssociationField::new('supplier', 'Fournisseur')
                ->setRequired(false),
            AssociationField::new('validated_user', 'Chef de projet')
                ->setRequired(true),
            MoneyField::new('price')
                ->setStoredAsCents(false)
                ->setNumDecimals(2)
                ->setRequired(false)
                ->setCurrency('EUR')->setLabel('Prix HT'),
        ];

        switch ($pageName) {
            case Action::INDEX :
                $return[] = BooleanField::new('validated', 'Facture validée ?')
                    ->setDisabled(true);
                break;
        }

        return $return;
    }

}
