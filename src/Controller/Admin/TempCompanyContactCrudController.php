<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\TempCompanyContact;
use App\Form\Type\TempTransfertContact;
use App\Repository\TempCompanyContactRepository;
use App\Service\SecurityChecker;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\LanguageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

class TempCompanyContactCrudController extends BaseCrudController
{
    public function __construct(private AdminUrlGenerator $adminUrlGenerator, protected SecurityChecker $securityChecker, private TempCompanyContactRepository $companyContactRepository)
    {
        parent::__construct($securityChecker);
    }

    public static function getEntityFqcn(): string
    {
        return TempCompanyContact::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud->setEntityLabelInPlural('Clients')
            ->setEntityLabelInSingular('Client')
            ->showEntityActionsInlined(true)
            ->overrideTemplate('crud/detail', 'admin/crud/detail_2cols.html.twig');

        return parent::configureCrud($crud);
    }

    public function configureFields(string $pageName): iterable
    {
        $panel1 = FormField::addPanel('Société')->setCustomOption('cols', 1);
        $panel2 = FormField::addPanel('Contact')->setCustomOption('cols', 2);
        $company = TextField::new('company.name')->setRequired(true)->setLabel('Société');
        $companyStreet = TextField::new('company.street')->setLabel('Rue')->setColumns(12);
        $companyPc = TextField::new('company.pc')->setLabel('Code postal');
        $companyCity = TextField::new('company.city')->setLabel('Ville')->setColumns(12);
        $companyCountry = CountryField::new('company.country')->setLabel('Pays');
        $companyVat = TextField::new('company.vat_number')->setLabel('Numéro de TVA')->setHelp('Mettre au format Européen (BE0760577097)');
        $firstname = TextField::new('firstname')->setLabel('Prénom')->setRequired(true)->setColumns(12);
        $lastname = TextField::new('lastname')->setLabel('Nom')->setRequired(true)->setColumns(12);
        $fullname = TextField::new('fullName')->setLabel('Nom');
        $lang = LanguageField::new('lang')->setLabel('Langue')->setRequired(true)->setColumns(12)->includeOnly(['fr', 'nl', 'en', 'it', 'es', 'de']);
        $langListing = LanguageField::new('lang')->setLabel('Lang')->showCode()->showName(false)->setRequired(false)->setColumns(12);
        $email = EmailField::new('email')->setLabel('E-mail')->setColumns(12)->setRequired(true);
        $phone = TelephoneField::new('phone')->setLabel('Téléphone')->setColumns(12)->setHelp('Mettre au format international (+32499163111) ');
        $userStreet = TextField::new('street')->setLabel('Rue')->setColumns(12);
        $userPc = TextField::new('pc')->setLabel('Code postal')->setColumns(12);
        $userCity = TextField::new('city')->setLabel('Ville')->setColumns(12);
        $userCountry = CountryField::new('country')->setLabel('Pays')->setColumns(12);

        $fonction = TextField::new('function')->setLabel('Fonction')->setColumns(12);

        $gsm = TelephoneField::new('gsm')->setLabel('Gsm')->setColumns(12)->setHelp('Mettre au format international (+32499163111) ');

        $user = TextField::new('added_by')->setColumns(12)->setRequired(false)
            ->setLabel('Commercial')->setDisabled(true);

        $userName = TextField::new('added_by.fullName')->setLabel('Commercial');
        $userNameListing = TextField::new('added_by.fullNameMinified')->setLabel('Sales');

        switch ($pageName) {
            case Crud::PAGE_EDIT:
            case Crud::PAGE_DETAIL:
                $response = [$panel1, $company, $companyVat, $companyStreet, $companyPc, $companyCity, $companyCountry, $panel2, $firstname, $lastname, $fonction, $lang, $email, $phone, $gsm, $userStreet, $userPc, $userCity, $userCountry, $user];
                break;
            case Crud::PAGE_INDEX:
                $response = [$company, $companyVat, $fullname, $langListing, $email, $phone, $gsm, $userNameListing];
                break;
            default:
                $response = [$company, $firstname, $lastname, $lang, $email, $phone];
        }

        return $response;
    }

    public function configureActions(Actions $actions): Actions
    {
        $transfert = Action::new('transfert', 'Transférer le contact')
            ->linkToCrudAction('transfertContact')
            ->displayIf(function (TempCompanyContact $entity) {

                if (empty($entity->getAddedBy())) {
                    return true;
                }
                return ($entity->getAddedBy()->getUserIdentifier() === $this->getUser()->getUserIdentifier());
            });


        $actions = parent::configureActions($actions);
        $actions
            ->add(Crud::PAGE_DETAIL, $transfert)
            ->setPermission(Action::EDIT, 'ROLE_COMMERCIAL')
            ->setPermission(Action::DELETE, 'ROLE_COMMERCIAL')
            ->setPermission(Action::DETAIL, 'ROLE_COMMERCIAL')
            ->setPermission(Action::INDEX, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_RETURN, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_ADD_ANOTHER, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_CONTINUE, 'ROLE_COMMERCIAL')
            ->update(Crud::PAGE_DETAIL, Action::DELETE,
                function (Action $action) {
                    return $action->displayIf(function (TempCompanyContact $entity) {
                        if (empty($entity->getAddedBy())) {
                            return true;
                        }
                        return ($entity->getAddedBy()->getUserIdentifier() === $this->getUser()->getUserIdentifier());
                    });
                }
            )
            ->update(Crud::PAGE_DETAIL, Action::EDIT,
                function (Action $action) {
                    return $action->displayIf(function (TempCompanyContact $entity) {
                        if (empty($entity->getAddedBy())) {
                            return true;
                        }
                        return ($entity->getAddedBy()->getUserIdentifier() === $this->getUser()->getUserIdentifier());
                    });
                }
            )
            ->disable(Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fa fa-eye')->setLabel(false)->setHtmlAttributes(['title' => 'Consulter']);
            })
        ;

        return $actions;
    }

    /**
     * @return RedirectResponse|KeyValueStore|Response
     */
    public function new(AdminContext $context)
    {
        $url = $this->adminUrlGenerator
            ->setController(TempCompanyCrudController::class)
            ->setAction(Action::NEW)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function transfertContact(AdminContext $context)
    {
        $form = $this->createForm(TempTransfertContact::class, $context->getEntity()->getInstance());

        $form->handleRequest($context->getRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            $this->companyContactRepository->save($form->getData(), true);
            return $this->redirect($context->getReferrer());
        }

        return $this->render('admin/contact/action_transfert.html.twig', [
            'contact' => $context->getEntity()->getInstance(),
            'form' => $form,
            'referrer' => $context->getReferrer()
        ]);
    }
}
