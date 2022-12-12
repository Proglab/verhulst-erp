<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserCrudController extends BaseCrudController
{
    #[IsGranted(data: User::ROLE_USER, message: 'Vous devez être authentifié pour accéder à cette page !')]
    #[Route(path: '/admin/modifier-mon-mot-de-passe', name: 'admin_password_update')]
    public function updatePassword(Request $request): RedirectResponse|Response
    {
        return $this->renderForm('admin/update_password.html.twig');
    }

    #[IsGranted(data: User::ROLE_USER)]
    #[Route(path: '/authentification-2-facteurs', name: 'admin_2fa_enable')]
    public function enable2fa(Request $request): Response
    {
        return $this->renderForm('admin/enable2fa.html.twig');
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

        switch ($pageName) {
            case Crud::PAGE_DETAIL:
            case Crud::PAGE_INDEX:
                $response = [$email, $firstname, $lastname, $locale, $twoFa];
                break;
            case Crud::PAGE_NEW:
            case Crud::PAGE_EDIT:
                $response = [$email, $firstname, $lastname, $locale];
                break;
            default:
                $response = [$email, $firstname, $lastname, $locale, $twoFa];
        }

        return $response;
    }
}
