<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\Filter\AddedByFilter;
use App\Entity\CompanyContact;
use App\Entity\User;
use App\Form\Type\TransfertContact;
use App\Repository\CompanyContactRepository;
use App\Repository\UserRepository;
use App\Service\SecurityChecker;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\LanguageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CompanyContactCrudController extends BaseCrudController
{
    public function __construct(private AdminUrlGenerator $adminUrlGenerator, protected SecurityChecker $securityChecker, private CompanyContactRepository $companyContactRepository)
    {
        parent::__construct($securityChecker);
    }

    public static function getEntityFqcn(): string
    {
        return CompanyContact::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud->setEntityLabelInPlural('Clients')
            ->setEntityLabelInSingular('Client')
            ->showEntityActionsInlined(true)
            ->overrideTemplate('crud/detail', 'admin/crud/detail_2cols.html.twig')
        ->setSearchFields(['firstname', 'lastname', 'company.name', 'email', 'phone', 'gsm', 'note', 'lang', 'company.vat_number']);

        return parent::configureCrud($crud);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(AddedByFilter::new('added_by')->setLabel('Commercial'))
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $panel1 = FormField::addPanel('Société')->setCustomOption('cols', 1);
        $panel2 = FormField::addPanel('Contact')->setCustomOption('cols', 2);
        $company = TextField::new('company')->setRequired(true)->setLabel('Société');
        $companyStreet = TextField::new('company.street')->setLabel('Rue')->setColumns(12)->setRequired(false);
        $companyPc = TextField::new('company.pc')->setLabel('Code postal')->setRequired(false);
        $companyCity = TextField::new('company.city')->setLabel('Ville')->setColumns(12)->setRequired(false);
        $companyCountry = CountryField::new('company.country')->setLabel('Pays')->setRequired(false);
        $companyVat = TextField::new('company.vat_number')->setLabel('Numéro de TVA')->setRequired(false);
        $companyVatNa = BooleanField::new('company.vat_na')->setLabel('Non assujetti')->setRequired(false);
        $firstname = TextField::new('firstname')->setLabel('Prénom')->setRequired(true)->setColumns(12);
        $lastname = TextField::new('lastname')->setLabel('Nom')->setRequired(true)->setColumns(12);
        $fullname = TextField::new('fullName')->setLabel('Nom');
        $lang = LanguageField::new('lang')->setLabel('Langue')->setRequired(true)->setColumns(12)->includeOnly(['fr', 'nl', 'en', 'it', 'es', 'de'])->setFormTypeOption('preferred_choices', ['fr']);
        $langListing = LanguageField::new('lang')->setLabel('Lang')->showCode()->showName(false)->setRequired(true)->setColumns(12);
        $email = EmailField::new('email')->setLabel('E-mail')->setColumns(12)->setRequired(true);
        $phone = TelephoneField::new('phone')->setLabel('Téléphone')->setColumns(12);
        $userStreet = TextField::new('street')->setLabel('Rue')->setColumns(12);
        $userPc = TextField::new('pc')->setLabel('Code postal')->setColumns(12);
        $userCity = TextField::new('city')->setLabel('Ville')->setColumns(12);
        $userCountry = CountryField::new('country')->setLabel('Pays')->setColumns(12);
        $interest = ChoiceField::new('interests')->setChoices([
            'Vip Sport' => ['Vip Football' => 'vip_football', 'Vip Tennis' => 'vip_tennis', 'Vip Padel' => 'vip_padel', 'Vip F1' => 'vip_f1', 'Vip Hockey' => 'hockey'],
            'Vip Culturel' => ['Concert' => 'concert', 'Gastro' => 'gastro'],
            'Event a la carte' => ['Teambuilding' => 'teambuilding', 'Incentive' => 'incentive', 'Family day' => 'family_day'],
            'Sponsoring / LEd' => ['Football' => 'sponsoring_football', 'Hockey' => 'sponsoring_hockey', 'Athlètes' => 'sponsoring_athletes', 'Led Boarding' => 'sponsoring_led_boarding'],
        ])->allowMultipleChoices(true)->setColumns(12)->setLabel('Intérêts')->setRequired(false);

        $fonction = TextField::new('function')->setLabel('Fonction')->setColumns(12);

        $gsm = TelephoneField::new('gsm')->setLabel('Gsm')->setColumns(12);
        $note = TextEditorField::new('note')->setLabel('Note')->setColumns(12);
        $noteView = TextField::new('note')->setLabel('Note')->renderAsHtml();

        $panel3 = FormField::addPanel('Facturation')->setCustomOption('cols', 1);

        $billingstreet = TextField::new('company.billing_street')->setRequired(true)->setColumns(12)->setLabel('Rue');
        $billingPc = TextField::new('company.billing_pc')->setRequired(true)->setColumns(12)->setLabel('Code postal');
        $billingcity = TextField::new('company.billing_city')->setRequired(true)->setColumns(12)->setLabel('Ville');
        $billingcountry = CountryField::new('company.billing_country')->setRequired(true)->setLabel('Pays');
        $user = TextField::new('added_by')->setColumns(12)->setRequired(false)
            ->setLabel('Commercial')->setDisabled(true);

        $userAdd = AssociationField::new('added_by')->setColumns(12)->setRequired(false)
        ->setLabel('Commercial')->setQueryBuilder(
            function (QueryBuilder $queryBuilder) {
                /** @var UserRepository $repo */
                $repo = $queryBuilder->getEntityManager()->getRepository(User::class);

                return $repo->getCommercialsQb();
            }
        );

        $userName = TextField::new('added_by.fullName')->setLabel('Commercial');
        $userNameListing = TextField::new('added_by.fullNameMinified')->setLabel('Sales');

        $panel4 = FormField::addPanel('To do')->setCustomOption('cols', 1);
        $items = AssociationField::new('todos')->setTemplatePath('admin/contact/crud.detail.html.twig')->setLabel(false);

        $response = match ($pageName) {
            Crud::PAGE_EDIT => [$firstname, $lastname, $lang, $email, $phone, $gsm, $userStreet, $userPc, $userCity, $userCountry, $fonction, $interest, $note, $user],
            Crud::PAGE_NEW => [$firstname, $lastname, $lang, $email, $phone, $gsm, $userStreet, $userPc, $userCity, $userCountry->setFormTypeOption('preferred_choices', ['BE']), $fonction, $interest, $note, $userAdd],
            Crud::PAGE_DETAIL => [$panel1, $company, $companyVat, $companyVatNa, $companyStreet, $companyPc, $companyCity, $companyCountry, $panel2, $fullname, $fonction, $lang, $email, $phone, $gsm, $userStreet, $userPc, $userCity, $userCountry, $interest, $userName, $noteView, $panel3, $billingstreet, $billingPc, $billingcity, $billingcountry, $panel4, $items],
            Crud::PAGE_INDEX => [$company, $companyVat, $fullname, $langListing, $email, $phone, $gsm, $userNameListing, $note],
            default => [$company, $firstname, $lastname, $lang, $email, $phone, $note],
        };

        return $response;
    }

    public function configureActions(Actions $actions): Actions
    {
        $transfert = Action::new('transfert', 'Transférer le contact')
            ->linkToCrudAction('transfertContact')
            ->displayIf(function (CompanyContact $entity) {
                if (empty($entity->getAddedBy())) {
                    return true;
                }

                if ($entity->getAddedBy()->getUserIdentifier() === $this->getUser()->getUserIdentifier()) {
                    return true;
                }

                if ($this->isGranted('ROLE_ADMIN')) {
                    return true;
                }

                return false;
            });

        $createTodo = Action::new('todo', 'Créer une Todo')
            ->linkToCrudAction('createTodo');

        $export = Action::new('export', 'Exporter les contacts')
            ->linkToCrudAction('export')->createAsGlobalAction();

        $actions = parent::configureActions($actions);
        $actions
            ->add(Crud::PAGE_DETAIL, $transfert)
            ->add(Crud::PAGE_DETAIL, $createTodo)
            ->setPermission(Action::NEW, 'ROLE_COMMERCIAL')
            ->setPermission(Action::EDIT, 'ROLE_COMMERCIAL')
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
            ->setPermission(Action::DETAIL, 'ROLE_COMMERCIAL')
            ->setPermission(Action::INDEX, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_RETURN, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_ADD_ANOTHER, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_CONTINUE, 'ROLE_COMMERCIAL')
            ->remove(crud::PAGE_DETAIL, Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fa fa-eye')->setLabel(false)->setHtmlAttributes(['title' => 'Consulter']);
            })
            ->add(Crud::PAGE_INDEX, $export)
            ->update(Crud::PAGE_INDEX, 'export', function (Action $action) {
                return $action->setIcon('fa fa-file-export')->setHtmlAttributes(['title' => 'Exporter']);
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
            ->setController(CompanyCrudController::class)
            ->setAction(Action::NEW)
            ->generateUrl();

        return $this->redirect($url);
    }

    /**
     * @return RedirectResponse|KeyValueStore|Response
     */
    public function edit(AdminContext $context)
    {
        /** @var CompanyContact $contact */
        $contact = $context->getEntity()->getInstance();
        $url = $this->adminUrlGenerator
            ->setController(CompanyCrudController::class)
            ->setAction(Action::EDIT)
            ->setEntityId($contact->getCompany()->getId())
            ->generateUrl();

        return $this->redirect($url);
    }

    public function transfertContact(AdminContext $context): RedirectResponse|Response
    {
        $form = $this->createForm(TransfertContact::class, $context->getEntity()->getInstance());

        $form->handleRequest($context->getRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            $this->companyContactRepository->save($form->getData(), true);

            return $this->redirect($context->getReferrer());
        }

        return $this->render('admin/contact/action_transfert.html.twig', [
            'contact' => $context->getEntity()->getInstance(),
            'form' => $form,
            'referrer' => $context->getReferrer(),
        ]);
    }

    public function createTodo(AdminContext $context): RedirectResponse|Response
    {
        return $this->redirect($this->adminUrlGenerator->setController(TodoCrudController::class)->setAction(Crud::PAGE_NEW)->setEntityId(null)->set('client_id', $context->getEntity()->getInstance()->getId())->generateUrl());
    }

    public function export(AdminContext $context): StreamedResponse
    {
        $contacts = $this->companyContactRepository->findBy(['added_by' => $this->getUser()]);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getNumberFormat()->setFormatCode('#');
        $activeWorksheet = $spreadsheet->getActiveSheet();

        $activeWorksheet->setCellValue('A1', 'Société');
        $activeWorksheet->setCellValue('B1', 'Nom');
        $activeWorksheet->setCellValue('C1', 'Prénom');
        $activeWorksheet->setCellValue('D1', 'Email');
        $activeWorksheet->setCellValue('E1', 'Téléphone');
        $activeWorksheet->setCellValue('F1', 'Gsm');
        $activeWorksheet->setCellValue('G1', 'Langue');

        $row = '2';

        foreach ($contacts as $contact) {
            $activeWorksheet->setCellValue('A' . $row, $contact->getCompany()->getName());
            $activeWorksheet->setCellValue('B' . $row, $contact->getLastname());
            $activeWorksheet->setCellValue('C' . $row, $contact->getFirstname());
            $activeWorksheet->setCellValue('D' . $row, $contact->getEmail());
            $activeWorksheet->setCellValueExplicit('E' . $row, $contact->getPhone(), DataType::TYPE_STRING);
            $activeWorksheet->setCellValueExplicit('F' . $row, $contact->getGsm(), DataType::TYPE_STRING);
            $activeWorksheet->setCellValue('G' . $row, $contact->getLang());

            ++$row;
        }

        $xlsxWriter = new Xlsx($spreadsheet);

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename_' . time() . '.xlsx"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($xlsxWriter) {
            $xlsxWriter->save('php://output');
        });

        return $response;
    }
}
