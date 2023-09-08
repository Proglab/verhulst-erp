<?php

declare(strict_types=1);

namespace App\Controller\Admin\Budget;

use App\Entity\Budget\Event;
use App\Repository\Budget\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\PercentField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EventArchivedCrudController extends BaseCrudController
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private AdminUrlGenerator $adminUrlGenerator)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Event::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInPlural('Archives : Evènements')
            ->setEntityLabelInSingular('Archive : Evènement')
            ->showEntityActionsInlined(true)
            ->overrideTemplate('crud/detail', 'admin/budget/events/archived.html.twig');

        return $crud;
    }

    public function configureActions(Actions $actions): Actions
    {
        $user = $this->getUser();

        $archiveEvent = Action::new('archiveEvent', false, 'fas fa-box-open')->linkToCrudAction('archive')->setHtmlAttributes(['title' => 'Désarchiver']);

        $actions = parent::configureActions($actions);
        $actions->add(Crud::PAGE_INDEX, Action::DETAIL);
        $actions->update(Crud::PAGE_INDEX, Action::DETAIL, function ($action) {
            return $action->setIcon('fa fa-eye')->setLabel(false)->setHtmlAttributes(['title' => 'Voir']);
        });

        $actions->setPermission(Action::INDEX, 'ROLE_BUDGET');
        $actions->setPermission(Action::EDIT, 'ROLE_BUDGET');
        $actions->setPermission(Action::DETAIL, 'ROLE_BUDGET');

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
        $actions->disable(Action::DELETE);
        $actions->disable(Action::EDIT);
        $actions->disable(Action::NEW);

        $actions->add(Crud::PAGE_INDEX, $archiveEvent);
        $actions->add(Crud::PAGE_DETAIL, $archiveEvent);

        $actions->update(Crud::PAGE_DETAIL, 'archiveEvent', function ($action) {
            return $action->setLabel('Désarchiver')->setHtmlAttributes(['title' => 'Désarchiver']);
        });
        $actions->setPermission('archiveEvent', 'event-unarchive');

        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        $name = TextField::new('name')->setLabel('Nom de l\'event');
        $admin = AssociationField::new('admin')->setQueryBuilder(
            function ($qb) {
                return $qb->andWhere('entity.roles LIKE :role')
                    ->setParameter('role', '%ROLE_ADMIN_BUDGET%');
            }
        )->setLabel('Chef de projet');
        $assistants = AssociationField::new('users')->setQueryBuilder(
            function ($qb) {
                return $qb->andWhere('entity.roles LIKE :role')
                    ->setParameter('role', '%ROLE_BUDGET%');
            }
        )->setLabel('Assistants');

        $assistantsList = CollectionField::new('users')->setLabel('Assistants');
        $date = DateField::new('date')->setFormat('dd/MM/yyyy');
        $percent = PercentField::new('percent')->setLabel('Pourcentage de commission')->setNumDecimals(2)->setStoredAsFractional(false);

        return match ($pageName) {
            Crud::PAGE_INDEX, Crud::PAGE_DETAIL => [$name, $date, $percent, $admin, $assistantsList],
            default => [$name, $date, $percent, $admin, $assistants],
        };
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters); // TODO: Change the autogenerated stub
        $qb->andWhere('entity.archived = 1');

        return $qb;
    }

    public function archive(AdminContext $context): RedirectResponse
    {
        /** @var Event $order */
        $order = $context->getEntity()->getInstance();
        $order->setArchived(false);
        /** @var EventRepository $repo */
        $repo = $this->entityManager->getRepository(Event::class);
        $repo->save($order, true);

        $url = $this->adminUrlGenerator->setController(self::class)->setAction(Action::INDEX)->setEntityId(null)->generateUrl();

        return $this->redirect($url);
    }
}
