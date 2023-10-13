<?php

declare(strict_types=1);

namespace App\Controller\Admin\Budget;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\HttpFoundation\RedirectResponse;

abstract class BaseCrudController extends AbstractCrudController
{
    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addHtmlContentToHead('<style>table > thead > tr > th {
    background: #000080   !important;
}</style>');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa fa-pencil-alt')->setLabel(false)->setHtmlAttributes(['title' => 'Modifier']);
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setIcon('fa fa-trash')->setLabel(false)->setHtmlAttributes(['title' => 'Supprimer']);
            })
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setIcon('fa fa-save');
            })
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setIcon('fa fa-plus-square');
            })
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ->setPermission(Action::NEW, 'ROLE_ADMIN_BUDGET')
            ->setPermission(Action::EDIT, 'ROLE_ADMIN_BUDGET')
            ->setPermission(Action::DELETE, 'ROLE_ADMIN_BUDGET')
            ->setPermission(Action::DETAIL, 'ROLE_ADMIN_BUDGET')
            ->setPermission(Action::INDEX, 'ROLE_ADMIN_BUDGET')
            ->setPermission(Action::SAVE_AND_RETURN, 'ROLE_ADMIN_BUDGET')
            ->disable(Action::BATCH_DELETE)
            ->disable(Action::SAVE_AND_CONTINUE)
            ->disable(Action::SAVE_AND_ADD_ANOTHER)
        ;
    }
}
