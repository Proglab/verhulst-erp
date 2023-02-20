<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserCrudController extends BaseCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        $crud->setEntityLabelInPlural('Utilisateurs')
            ->setEntityLabelInSingular('Utilisateur')
            ->showEntityActionsInlined(true);

        return parent::configureCrud($crud);
    }

    #[Route(path: '/admin/modifier-mon-mot-de-passe', name: 'admin_password_update')]
    public function updatePassword(): RedirectResponse|Response
    {
        return $this->renderForm('admin/update_password.html.twig');
    }

    #[Route(path: '/admin/authentification-2-facteurs', name: 'admin_2fa_enable')]
    public function enable2fa(): Response
    {
        return $this->renderForm('admin/enable2fa.html.twig');
    }

    #[Route(path: '/admin/update_profile', name: 'admin_update_profile')]
    public function updateProfile(): Response
    {
        return $this->renderForm('admin/update_profile.html.twig');
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $email = EmailField::new('email');
        $firstname = TextField::new('firstName');
        $lastname = TextField::new('lastName');
        $locale = ChoiceField::new('locale')->allowMultipleChoices(false)->renderExpanded(true)->setChoices(['Français' => 'fr', 'English' => 'en']);
        $twoFa = BooleanField::new('isTotpEnabled');
        $role = ChoiceField::new('roles')->allowMultipleChoices(true)->renderExpanded(true)->setChoices(['Admin' => 'ROLE_ADMIN', 'Commercial' => 'ROLE_COMMERCIAL']);
        $enabled = BooleanField::new('enabled');
        $freelance = BooleanField::new('freelance');

        switch ($pageName) {
            case Crud::PAGE_DETAIL:
            case Crud::PAGE_INDEX:
                $response = [$firstname, $lastname, $email, $locale, $twoFa, $role, $freelance, $enabled];
                break;
            case Crud::PAGE_NEW:
            case Crud::PAGE_EDIT:
                $response = [$firstname, $lastname, $email, $locale, $role, $freelance, $enabled];
                break;
            default:
                $response = [$firstname, $lastname, $email, $locale, $twoFa, $role, $freelance, $enabled];
        }

        return $response;
    }

    /**
     * @param ?object $entityInstance
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $entityManager->persist($entityInstance);
        $entityManager->flush();
    }

    /**
     * @param ?object $entityInstance
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /* @var User $entityInstance */
        $entityInstance->setPassword('Password123!');
        $entityManager->persist($entityInstance);
        $entityManager->flush();
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        /** @var QueryBuilder $qb */
        $qb = $this->container->get(EntityRepository::class)->createQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $qb->andWhere('entity.roles NOT LIKE :searchTerm')
            ->setParameter('searchTerm', '%ROLE_BOSS%');

        return $qb;
    }
}
