<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Project;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class ProjectCrudController extends BaseCrudController
{
    public function __construct(private MailerInterface $mailer)
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
        $name = TextField::new('name');
        $projectEvent = CollectionField::new('product_event')->setLabel('Event à la carte')->allowAdd(true)->allowDelete(true)->setEntryIsComplex()->useEntryCrudForm(ProductEventCrudController::class);
        $projectPackage = CollectionField::new('product_package')->setLabel('Package VIP')->allowAdd(true)->allowDelete(true)->setEntryIsComplex()->useEntryCrudForm(ProductPackageVipCrudController::class);
        $projectSponsor = CollectionField::new('product_sponsoring')->setLabel('Sponsoring')->allowAdd(true)->allowDelete(true)->setEntryIsComplex()->useEntryCrudForm(ProductSponsoringCrudController::class);
        $projectDivers = CollectionField::new('product_divers')->setLabel('Divers')->allowAdd(true)->allowDelete(true)->setEntryIsComplex()->useEntryCrudForm(ProductDiversCrudController::class);
        $mail = BooleanField::new('mail')->setLabel('Prévenir les commerciaux ?');

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
        $actions
            ->setPermission(Action::NEW, 'ROLE_COMMERCIAL')
            ->setPermission(Action::EDIT, 'ROLE_COMMERCIAL')
            ->setPermission(Action::DELETE, 'ROLE_COMMERCIAL')
            ->setPermission(Action::DETAIL, 'ROLE_COMMERCIAL')
            ->setPermission(Action::INDEX, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_RETURN, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_ADD_ANOTHER, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_CONTINUE, 'ROLE_COMMERCIAL')
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fa fa-eye');
            })
        ;

        return $actions;
    }

    /**
     * @param ?object $entityInstance
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
                    ->from('info@verhulst.be')
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

    public function detail(AdminContext $context)
    {
        $project = $context->getEntity();
        return $this->render('admin/project/detail.html.twig', [
            'project' => $project->getInstance()
        ]);
    }
}
