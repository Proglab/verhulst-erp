<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\LocaleField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends BaseCrudController
{
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
