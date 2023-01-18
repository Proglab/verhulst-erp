<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\CompanyContact;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CompanyContactCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CompanyContact::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $firstname = TextField::new('firstname')->setRequired(true)->setColumns(12);
        $lastname = TextField::new('lastname')->setRequired(true)->setColumns(12);
        $lang = ChoiceField::new('lang')->allowMultipleChoices(false)->renderExpanded(false)->setChoices(['Français' => 'fr', 'Nederlands' => 'nl', 'English' => 'en'])->setRequired(true)->setColumns(12);
        $email = TextField::new('email')->setColumns(12);
        $phone = TextField::new('phone')->setColumns(12);

        $response = [$firstname, $lastname, $lang, $email, $phone];

        return $response;
    }
}
