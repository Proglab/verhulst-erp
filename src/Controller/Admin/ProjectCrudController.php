<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Project;
use App\Entity\User;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;

class ProjectCrudController extends BaseCrudController
{
    public function __construct(private MailerInterface $mailer, private ProjectRepository $projectRepository, private AdminUrlGenerator $adminUrlGenerator)
    {
    }

    public function configureAssets(Assets $assets): Assets
    {
        $assets = parent::configureAssets($assets);
        $assets->addWebpackEncoreEntry('wysiwyg_css');
        $assets->addWebpackEncoreEntry('wysiwyg_js');

        return $assets;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud->setEntityLabelInPlural('Projets')
            ->setEntityLabelInSingular('Projet')
            ->showEntityActionsInlined(true);

        return parent::configureCrud($crud);
    }

    public static function getEntityFqcn(): string
    {
        return Project::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $name = TextField::new('name')->setLabel('Nom du projet');

        if ($this->isGranted('ROLE_ENCODE')) {
            $projectEvent = CollectionField::new('product_event')->setLabel('Event à la carte')->allowAdd(true)->allowDelete(true)->setEntryIsComplex()->useEntryCrudForm(ProductEventCrudController::class)->setRequired(true);
            $projectPackage = CollectionField::new('product_package')->setLabel('Package VIP')->allowAdd(true)->allowDelete(true)->setEntryIsComplex()->useEntryCrudForm(ProductPackageVipCrudController::class)->setRequired(true);
            $projectSponsor = CollectionField::new('product_sponsoring')->setLabel('Sponsoring')->allowAdd(true)->allowDelete(true)->setEntryIsComplex()->useEntryCrudForm(ProductSponsoringCrudController::class)->setRequired(true);
            $projectDivers = CollectionField::new('product_divers')->setLabel('Divers')->allowAdd(true)->allowDelete(true)->setEntryIsComplex()->useEntryCrudForm(ProductDiversCrudController::class)->setRequired(true);
        } else {
            $projectEvent = CollectionField::new('product_event')->setLabel('Event à la carte')->allowAdd(true)->allowDelete(false)->setEntryIsComplex()->useEntryCrudForm(ProductEventCrudController::class)->setRequired(true);
            $projectPackage = CollectionField::new('product_package')->setLabel('Package VIP')->allowAdd(false)->allowDelete(false)->setEntryIsComplex()->useEntryCrudForm(ProductPackageVipCrudController::class)->setRequired(true);
            $projectSponsor = CollectionField::new('product_sponsoring')->setLabel('Sponsoring')->allowAdd(false)->allowDelete(false)->setEntryIsComplex()->useEntryCrudForm(ProductSponsoringCrudController::class)->setRequired(true);
            $projectDivers = CollectionField::new('product_divers')->setLabel('Divers')->allowAdd(true)->allowDelete(false)->setEntryIsComplex()->useEntryCrudForm(ProductDiversCrudController::class)->setRequired(true);
        }

        $mail = BooleanField::new('mail')->setLabel('Prévenir les commerciaux ?')->setPermission('ROLE_ADMIN');

        switch ($pageName) {
            case Crud::PAGE_DETAIL:
            case Crud::PAGE_INDEX:
            case Crud::PAGE_EDIT:
                $response = [$name, $projectEvent, $projectSponsor, $projectPackage, $projectDivers];
                break;
            case Crud::PAGE_NEW:
                $response = [$name, $mail, $projectEvent, $projectSponsor, $projectPackage, $projectDivers];
                break;
            default:
                $response = [$name, $projectEvent, $projectSponsor, $projectPackage, $projectDivers];
        }

        return $response;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);

        $clone = Action::new('cloneProject', 'Clone project')
            ->linkToCrudAction('cloneProject');

        $actions
            ->setPermission(Action::NEW, 'ROLE_ENCODE')
            ->setPermission(Action::EDIT, 'ROLE_ENCODE')
            ->setPermission(Action::DELETE, 'ROLE_ENCODE')
            ->setPermission(Action::DETAIL, 'ROLE_COMMERCIAL')
            ->setPermission(Action::INDEX, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_RETURN, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_ADD_ANOTHER, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_CONTINUE, 'ROLE_COMMERCIAL')
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fa-solid fa-eye')->setLabel(false)->setHtmlAttributes(['title' => 'Consulter']);
            })
            ->add(Crud::PAGE_INDEX, $clone)
            ->update(Crud::PAGE_INDEX, 'cloneProject', function (Action $action) {
                return $action->setIcon('fa-solid fa-clone')->setLabel(false)->setHtmlAttributes(['title' => 'Clone']);
            })
        ;

        return $actions;
    }

    /**
     * @param ?Project $entityInstance
     *
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (true === $entityInstance->isMail()) {
            /** @var UserRepository $userRepo */
            $userRepo = $entityManager->getRepository(User::class);
            /** @var User[] $users */
            $users = $userRepo->getCommercials();

            foreach ($users as $user) {
                $email = (new TemplatedEmail())
                    ->from('info@verhulst.pro')
                    ->to($user->getEmail())
                    // ->priority(Email::PRIORITY_HIGH)
                    ->subject('Un nouveau projet a été créé')
                    ->htmlTemplate('email/admin/new_project/new_project.html.twig')
                    ->context([
                        'project' => $entityInstance,
                    ]);

                $this->mailer->send($email);
            }
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    /**
     * @return KeyValueStore|Response
     */
    public function detail(AdminContext $context)
    {
        $project = $context->getEntity();

        return $this->render('admin/project/detail.html.twig', [
            'project' => $project->getInstance(),
        ]);
    }

    public function cloneProject(AdminContext $context): RedirectResponse
    {
        /** @var Project $project */
        $project = $context->getEntity()->getInstance();

        $project_new = clone $project;

        foreach ($project->getProductEvent() as $event) {
            $doc = $event->getDoc();
            $eventClone = clone $event;
            if (null !== $doc) {
                $url = realpath($event->getUrl());
                $newName = uniqid('-') . '.pdf';
                $newUrl = str_replace('.pdf', $newName, $url);
                copy($url, $newUrl);
                $eventClone->setDoc(basename($newUrl));
            }
            $project_new->addProductEvent($eventClone);
        }
        foreach ($project->getProductPackage() as $event) {
            $doc = $event->getDoc();
            $eventClone = clone $event;
            if (null !== $doc) {
                $url = realpath($event->getUrl());
                $newName = uniqid('-') . '.pdf';
                $newUrl = str_replace('.pdf', $newName, $url);
                copy($url, $newUrl);
                $eventClone->setDoc(basename($newUrl));
            }
            $project_new->addProductPackage($eventClone);
        }
        foreach ($project->getProductSponsoring() as $event) {
            $doc = $event->getDoc();
            $eventClone = clone $event;
            if (null !== $doc) {
                $url = realpath($event->getUrl());
                $newName = uniqid('-') . '.pdf';
                $newUrl = str_replace('.pdf', $newName, $url);
                copy($url, $newUrl);
                $eventClone->setDoc(basename($newUrl));
            }
            $project_new->addProductSponsoring($eventClone);
        }
        foreach ($project->getProductDivers() as $event) {
            $doc = $event->getDoc();
            $eventClone = clone $event;
            if (null !== $doc) {
                $url = realpath($event->getUrl());
                $newName = uniqid('-') . '.pdf';
                $newUrl = str_replace('.pdf', $newName, $url);
                copy($url, $newUrl);
                $eventClone->setDoc(basename($newUrl));
            }
            $project_new->addProductDiver($eventClone);
        }
        $this->projectRepository->save($project_new, true);

        $url = $this->adminUrlGenerator
            ->setController(CommissionCrudController::class)
            ->setAction('index')
            ->setEntityId(null)
            ->generateUrl();

        $this->addFlash('warning', '⚠️⚠️⚠️N\'oubliez pas d\'encoder les commissions pour le projet <strong>' . $project_new->getName() . '</strong> !!! ⚠️⚠️⚠️');

        return $this->redirect($url);
    }
}
