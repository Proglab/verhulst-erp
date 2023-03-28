<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\TodoType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class TodoTypeCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return TodoType::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud->setEntityLabelInPlural('Types de to do')
            ->setEntityLabelInSingular('Type de to do')
            ->showEntityActionsInlined(true);

        return parent::configureCrud($crud);
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);
        $actions
            ->setPermission(Action::NEW, 'ROLE_ENCODE')
            ->setPermission(Action::EDIT, 'ROLE_ENCODE')
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
            ->setPermission(Action::DETAIL, 'ROLE_ENCODE')
            ->setPermission(Action::INDEX, 'ROLE_ENCODE')
            ->setPermission(Action::SAVE_AND_RETURN, 'ROLE_ENCODE')
            ->setPermission(Action::SAVE_AND_ADD_ANOTHER, 'ROLE_ENCODE')
            ->setPermission(Action::SAVE_AND_CONTINUE, 'ROLE_ENCODE')
            ->disable(Action::DETAIL)
        ;

        return $actions;
    }
}
