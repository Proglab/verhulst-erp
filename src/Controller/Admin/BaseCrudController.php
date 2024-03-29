<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Service\SecurityChecker;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseCrudController extends AbstractCrudController
{
    public function __construct(private SecurityChecker $securityChecker)
    {
    }

    public function configureActions(Actions $actions): Actions
    {
        $getVoters = Action::new('getVoters', null)
            ->linkToCrudAction('getVoters');

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
            ->setPermission(Action::NEW, 'ROLE_ADMIN')
            ->setPermission(Action::EDIT, 'ROLE_ADMIN')
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
            ->setPermission(Action::DETAIL, 'ROLE_ADMIN')
            ->setPermission(Action::INDEX, 'ROLE_ADMIN')
            ->setPermission(Action::SAVE_AND_RETURN, 'ROLE_ADMIN')
            ->setPermission(Action::SAVE_AND_ADD_ANOTHER, 'ROLE_ADMIN')
            ->setPermission(Action::SAVE_AND_CONTINUE, 'ROLE_ADMIN')
            ->setPermission('getVoters', 'IS_AUTHENTICATED_FULLY')
            ->disable(Action::BATCH_DELETE)
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
        ;
    }

    public function getVoters(AdminContext $adminContext): Response
    {
        $request = $adminContext->getRequest();
        $role = $request->get('role');
        $viewParams = [];

        if (null === $role) {
            $role = $this->getUser()->getRoles();
        }

        foreach ($adminContext->getCrud()->getActionsConfig()->getActionPermissions() as $action => $permission) {
            $viewParams[$action] = [
                'granted' => $this->securityChecker->isGrantedByRole($role, $permission) && !\in_array($action, $adminContext->getCrud()->getActionsConfig()->getDisabledActions(), true),
                'permission' => $permission,
            ];
        }

        return $this->render('admin/voters.html.twig',
            ['title' => $adminContext->getCrud()->getEntityFqcn(), 'datas' => $viewParams]
        );
    }
}
