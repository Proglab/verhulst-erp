<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\Field\DateIntervalField;
use App\Entity\Todo;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class TodoCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return Todo::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud->setEntityLabelInPlural('Todos')
            ->setEntityLabelInSingular('Todo')
            ->showEntityActionsInlined(true)
        ->setDefaultSort(['date_reminder' => 'ASC']);

        return parent::configureCrud($crud);
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);

        Action::new('getVatInfos', false)
            ->linkToCrudAction('getVatInfos');

        $actions
            ->setPermission('getVatInfos', 'ROLE_COMMERCIAL')
            ->setPermission(Action::NEW, 'ROLE_COMMERCIAL')
            ->setPermission(Action::EDIT, 'ROLE_COMMERCIAL')
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
            ->setPermission(Action::DETAIL, 'ROLE_COMMERCIAL')
            ->setPermission(Action::INDEX, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_RETURN, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_ADD_ANOTHER, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_CONTINUE, 'ROLE_COMMERCIAL')
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fa fa-eye')->setLabel(false)->setHtmlAttributes(['title' => 'Consulter']);
            })
        ;

        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        $dateReminder = DateTimeField::new('date_reminder')->setLabel('Date rappel')->setRequired(true)->setFormat('dd/MM/yy hh:mm');
        $tempsRestant = DateIntervalField::new('time_remaining')->setLabel('Temps restant')->setRequired(true);
        $contact = AssociationField::new('client')->setLabel('Client')->setRequired(true);
        $todo = TextareaField::new('todo')->setLabel('Todo')->setRequired(true);
        $done = BooleanField::new('done')->setLabel('Fait ?');
        $user = AssociationField::new('user')->setLabel('Sales')->setRequired(true);
        $dateDone = DateTimeField::new('date_done')->setLabel('Date de validation');

        if ($this->isGranted('ROLE_ADMIN')) {
            switch ($pageName) {
                case Crud::PAGE_NEW:
                case Crud::PAGE_EDIT:
                    $response = [$dateReminder, $user, $contact, $todo, $done];
                    break;
                case Crud::PAGE_INDEX:
                    $response = [$dateReminder, $tempsRestant, $user, $contact, $todo, $done];
                    break;
                case Crud::PAGE_DETAIL:
                    $response = [$dateReminder, $user, $contact, $todo, $done, $dateDone];
                    break;
                default:
                    $response = [$dateReminder, $user, $contact, $todo, $done];
            }
        } else {
            $response = [$dateReminder, $contact, $todo, $done];
        }

        return $response;
    }
}
