<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Commission;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use EasyCorp\Bundle\EasyAdminBundle\Factory\EntityFactory;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
use EasyCorp\Bundle\EasyAdminBundle\Factory\PaginatorFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\PercentField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use Symfony\Component\Routing\Annotation\Route;

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
