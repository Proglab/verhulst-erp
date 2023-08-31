<?php

namespace App\Controller\Admin\Budget;

use App\Entity\Budget\Event;
use App\Entity\User;
use App\Service\SecurityChecker;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EventCrudController extends BaseCrudController
{
    public function __construct(SecurityChecker $securityChecker, private EntityManagerInterface $entityManager)
    {
        parent::__construct($securityChecker);
    }

    public static function getEntityFqcn(): string
    {
        return Event::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInPlural('Evènements')
            ->setEntityLabelInSingular('Evènement')
            ->showEntityActionsInlined(true)
            ->overrideTemplate('crud/detail', 'admin/budget/events/details.html.twig');

        return $crud;
    }

    public function configureActions(Actions $actions): Actions
    {
        $user = $this->getUser();

        $actions = parent::configureActions($actions); 
        $actions->add(Crud::PAGE_INDEX, Action::DETAIL);
        $actions->update(Crud::PAGE_INDEX, Action::DETAIL, function ($action) {
            return $action->setIcon('fa fa-eye')->setLabel(false)->setHtmlAttributes(['title' => 'Voir']);
        });

        $actions->setPermission(Action::INDEX, 'ROLE_BUDGET');
        $actions->setPermission(Action::EDIT, 'ROLE_BUDGET');
        $actions->setPermission(Action::DETAIL, 'ROLE_BUDGET');

        $actions->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) use ($user) {
            return $action->displayIf(function ($entity) use ($user) {
                /** @var Event $entity */
                if ($entity->getAdmin() === $user || $entity->getUsers()->contains($user) || $this->isGranted('ROLE_ADMIN_BUDGET')) {
                    return true;
                }

                return false;
            });
        });

        $actions->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) use ($user) {
            return $action->displayIf(function ($entity) use ($user) {
                /** @var Event $entity */
                if ($entity->getAdmin() === $user || $this->isGranted('ROLE_ADMIN_BUDGET')) {
                    return true;
                }

                return false;
            });
        });

        $actions->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) use ($user) {
            return $action->displayIf(function ($entity) use ($user) {
                /** @var Event $entity */
                if ($entity->getAdmin() === $user || $entity->getUsers()->contains($user) || $this->isGranted('ROLE_ADMIN_BUDGET')) {
                    return true;
                }

                return false;
            });
        });


        $actions->disable(Action::SAVE_AND_ADD_ANOTHER);
        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        $name = TextField::new('name')->setLabel('Nom de l\'event');
        $userRepository = $this->entityManager->getRepository(User::class);
        $admin = AssociationField::new('admin')->setQueryBuilder(
            function($qb) {
                return $qb->andWhere('entity.roles LIKE :role')
                    ->setParameter('role', '%ROLE_ADMIN_BUDGET%');
            }
        )->setLabel('Responsable');
        $assistants = AssociationField::new('users')->setQueryBuilder(
            function($qb) {
                return $qb->andWhere('entity.roles LIKE :role')
                    ->setParameter('role', '%ROLE_BUDGET%');
            }
        )->setLabel('Assistants');

        $assistantsList = CollectionField::new('users')->setLabel('Assistants');

        return match ($pageName) {
            Crud::PAGE_INDEX, Crud::PAGE_DETAIL => [$name, $admin, $assistantsList],
            default => [$name, $admin, $assistants],
        };
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param Event $entityInstance
     * @return void
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $entityInstance->setAdmin($this->getUser());
        parent::persistEntity($entityManager, $entityInstance); 
    }

    public function detail(AdminContext $context)
    {
        $user = $this->getUser();
        $entity = $context->getEntity()->getInstance();
        if ($entity->getAdmin() === $user || $entity->getUsers()->contains($user) || $this->isGranted('ROLE_ADMIN_BUDGET')) {
            return parent::detail($context);
        }
        throw new AccessDeniedHttpException('Vous n\'avez pas le droit d\'accéder à cet event');
    }

    public function edit(AdminContext $context)
    {
        $user = $this->getUser();
        $entity = $context->getEntity()->getInstance();
        if ($entity->getAdmin() === $user || $entity->getUsers()->contains($user) || $this->isGranted('ROLE_ADMIN_BUDGET')) {
            return parent::edit($context); 
        }
        throw new AccessDeniedHttpException('Vous n\'avez pas le droit de modifier cet event');
    }

    public function delete(AdminContext $context)
    {
        $user = $this->getUser();
        $entity = $context->getEntity()->getInstance();
        if ($entity->getAdmin() === $user || $this->isGranted('ROLE_ADMIN_BUDGET')) {
            return parent::delete($context);
        }
        throw new AccessDeniedHttpException('Vous n\'avez pas le droit de supprimer cet event');
    }
}
