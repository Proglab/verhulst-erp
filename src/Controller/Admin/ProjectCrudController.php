<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ProductPackageVip;
use App\Entity\Project;
use App\Entity\User;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use App\Service\SecurityChecker;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;

class ProjectCrudController extends BaseCrudController
{
    public function __construct(protected MailerInterface $mailer, protected ProjectRepository $projectRepository, protected AdminUrlGenerator $adminUrlGenerator, protected SecurityChecker $securityChecker)
    {
        parent::__construct($securityChecker);
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
            ->showEntityActionsInlined(true)
        ->setDefaultSort(['name' => 'ASC']);

        return parent::configureCrud($crud);
    }

    public static function getEntityFqcn(): string
    {
        return Project::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $name = TextField::new('name')->setLabel('Nom du projet')
            ->setTemplatePath('admin/field/projects.html.twig');

        $dateBegin = DateField::new('date_begin')->setLabel('Du')->setRequired(true);
        $dateEnd = DateField::new('date_end')->setLabel('Au')->setRequired(true);

        $image = ImageField::new('doc')
            ->setBasePath($this->getParameter('files.projects.base_path'))
            ->setUploadDir($this->getParameter('files.projects.upload_dir'))
            ->setUploadedFileNamePattern('[slug]-[timestamp]-[randomhash].[extension]')
            ->setLabel('Document (PDF)');
        $imageDwonload = TextField::new('download_url')->renderAsHtml()->setLabel('Document (PDF)');

        if ($this->isGranted('ROLE_ENCODE')) {
            $projectPackage = CollectionField::new('product_package')->setLabel('Package VIP')->allowAdd(true)->allowDelete(true)->setEntryIsComplex()->useEntryCrudForm(ProductPackageVipCrudController::class)->setRequired(true);
            $projectSponsor = CollectionField::new('product_sponsoring')->setLabel('Sponsoring')->allowAdd(true)->allowDelete(true)->setEntryIsComplex()->useEntryCrudForm(ProductSponsoringCrudController::class)->setRequired(true);
        } else {
            $projectPackage = CollectionField::new('product_package')->setLabel('Package VIP')->allowAdd(false)->allowDelete(false)->setEntryIsComplex()->useEntryCrudForm(ProductPackageVipCrudController::class)->setRequired(true);
            $projectSponsor = CollectionField::new('product_sponsoring')->setLabel('Sponsoring')->allowAdd(false)->allowDelete(false)->setEntryIsComplex()->useEntryCrudForm(ProductSponsoringCrudController::class)->setRequired(true);
        }

        if ($this->isGranted('ROLE_ENCODE')) {
            $projectPackageIndex = AssociationField::new('product_package')->setLabel('Package VIP')->setRequired(true);
            $projectSponsorIndex = AssociationField::new('product_sponsoring')->setLabel('Sponsoring')->setRequired(true);
        } else {
            $projectPackageIndex = AssociationField::new('product_package')->setLabel('Package VIP')->setRequired(true);
            $projectSponsorIndex = AssociationField::new('product_sponsoring')->setLabel('Sponsoring')->setRequired(true);
        }

        $response = match ($pageName) {
            Crud::PAGE_INDEX => [$name, $dateBegin, $dateEnd, $projectSponsorIndex, $projectPackageIndex, $imageDwonload],
            Crud::PAGE_DETAIL => [$name, $dateBegin, $dateEnd,  $projectSponsorIndex, $projectPackageIndex,  $imageDwonload],
            Crud::PAGE_NEW, Crud::PAGE_EDIT => [$name, $dateBegin, $dateEnd,  $projectSponsor, $projectPackage,  $image],
            default => [$name, $dateBegin, $dateEnd,  $projectSponsor, $projectPackage],
        };

        return $response;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);

        $clone = Action::new('cloneProject', 'Clone project')
            ->linkToCrudAction('cloneProject');

        $archive = Action::new('archiveProject', 'Archive project')
            ->linkToCrudAction('archiveProject');

        $createEvent = Action::new('createEvent', 'Créer un event')
            ->linkToCrudAction('createEvent');

        $createEvent = Action::new('createPackage', 'Créer un package VIP')
            ->linkToCrudAction('createPackage');

        $actions
            ->setPermission('cloneProject', 'ROLE_ENCODE')
            ->setPermission('archiveProject', 'ROLE_COMMERCIAL')
            ->setPermission('createEvent', 'ROLE_COMMERCIAL')
            ->setPermission(Action::NEW, 'ROLE_ENCODE')
            ->setPermission(Action::EDIT, 'ROLE_ENCODE')
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
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
            ->add(Crud::PAGE_INDEX, $archive)
            ->update(Crud::PAGE_INDEX, 'archiveProject', function (Action $action) {
                return $action->setIcon('fa-solid fa-box-archive')->setLabel(false)->setHtmlAttributes(['title' => 'Archive']);
            })
            ->add(Crud::PAGE_DETAIL, $archive)
            ->update(Crud::PAGE_DETAIL, 'archiveProject', function (Action $action) {
                return $action->setIcon('fa-solid fa-box-archive')->setLabel(false)->setHtmlAttributes(['title' => 'Archive']);
            })
        ;

        return $actions;
    }

    public function new(AdminContext $context)
    {
        return $this->redirectToRoute('project_new');
    }

    /**
     * @param Project $entityInstance
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $days = $entityInstance->getDateEnd()->diff($entityInstance->getDateBegin());
        $days = $days->days;

        foreach ($entityInstance->getProductPackage() as $package) {
            if (null === $package->getDateBegin()) {
                /** @var \DateTime $date */
                $date = clone $entityInstance->getDateBegin();
                for ($i = 0; $i <= $days; ++$i) {
                    if (0 === $i) {
                        $packageNew = $package;
                        $packageNew->setDateBegin(clone $date);
                        $packageNew->setDateEnd(clone $date);
                    } else {
                        $packageNew = new ProductPackageVip();
                        $packageNew->setName($package->getName());
                        $packageNew->setPercentTv((string) $package->getPercentTv());
                        $packageNew->setDoc($package->getDoc());
                        $packageNew->setDescription($package->getDescription());
                        $packageNew->setPercentFreelance((string) $package->getPercentFreelance());
                        $packageNew->setPercentSalarie((string) $package->getPercentSalarie());
                        $packageNew->setPercentTv((string) $package->getPercentTv());
                        $packageNew->setProject($package->getProject());
                        $packageNew->setCa($package->getCa());
                        $packageNew->setPa($package->getPa());
                        $packageNew->setQuantityMax($package->getQuantityMax());
                        if ($i > 0) {
                            /* @var \DateTime $date */
                            $date->add(new \DateInterval('P1D'));
                        }
                        $packageNew->setDateBegin(clone $date);
                        $packageNew->setDateEnd(clone $date);
                        $entityInstance->addProductPackage($packageNew);
                    }
                }
            }
        }
        parent::updateEntity($entityManager, $entityInstance); // TODO: Change the autogenerated stub
    }

    /**
     * @param ?Project $entityInstance
     *
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $days = $entityInstance->getDateEnd()->diff($entityInstance->getDateBegin());
        $days = $days->days;

        foreach ($entityInstance->getProductPackage() as $package) {
            /** @var \DateTime $date */
            $date = clone $entityInstance->getDateBegin();
            for ($i = 0; $i <= $days; ++$i) {
                $packageNew = new ProductPackageVip();
                $packageNew->setName($package->getName());
                $packageNew->setPercentTv((string) $package->getPercentTv());
                $packageNew->setDoc($package->getDoc());
                $packageNew->setDescription($package->getDescription());
                $packageNew->setPercentFreelance((string) $package->getPercentFreelance());
                $packageNew->setPercentSalarie((string) $package->getPercentSalarie());
                $packageNew->setPercentTv((string) $package->getPercentTv());
                $packageNew->setPercentVr((string) $package->getPercentVr());
                $packageNew->setProject($package->getProject());
                $packageNew->setCa($package->getCa());
                $packageNew->setPa($package->getPa());
                $packageNew->setQuantityMax($package->getQuantityMax());
                if ($i > 0) {
                    /* @var \DateTime $date */
                    $date->add(new \DateInterval('P1D'));
                }
                $packageNew->setDateBegin(clone $date);
                $packageNew->setDateEnd(clone $date);
                $entityInstance->addProductPackage($packageNew);
            }

            $entityInstance->removeProductPackage($package);
        }

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
        return $this->redirect($this->generateUrl('project_details', ['project' => $context->getEntity()->getPrimaryKeyValue()]));
    }

    public function createPackage(AdminContext $context)
    {
        $project = $context->getEntity();

        return $this->render('admin/project/create_package_page.html.twig', [
            'project' => $project->getInstance(),
        ]);
    }

    public function createSponsoring(AdminContext $context)
    {
        $project = $context->getEntity();

        return $this->render('admin/project/create_sponsoring_page.html.twig', [
            'project' => $project->getInstance(),
        ]);
    }

    public function cloneProject(AdminContext $context): RedirectResponse
    {
        /** @var Project $project */
        $project = $context->getEntity()->getInstance();

        $project_new = clone $project;

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
        $project_new->setArchive(false);
        $this->projectRepository->save($project_new, true);

        $url = $this->adminUrlGenerator
            ->setController(CommissionCrudController::class)
            ->setAction('index')
            ->setEntityId(null)
            ->generateUrl();

        $this->addFlash('warning', '⚠️⚠️⚠️N\'oubliez pas d\'encoder les commissions pour le projet <strong>' . $project_new->getName() . '</strong> !!! ⚠️⚠️⚠️');

        return $this->redirect($url);
    }

    public function archiveProject(AdminContext $context): RedirectResponse
    {
        /** @var Project $project */
        $project = $context->getEntity()->getInstance();
        $project->setArchive(true);
        $this->projectRepository->save($project, true);

        $url = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction('index')
            ->setEntityId(null)
            ->generateUrl();

        $this->addFlash('success', 'Le projet <strong>' . $project->getName() . '</strong> a bien été archivé');

        return $this->redirect($url);
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        /** @var QueryBuilder $qb */
        $qb = $this->container->get(EntityRepository::class)->createQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $qb->andWhere('entity.archive = :archive')
            ->setParameter('archive', false);

        return $qb;
    }
}
