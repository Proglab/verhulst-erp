<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\Filter\AddedByFilter;
use App\Entity\CompanyContact;
use App\Entity\User;
use App\Form\Type\ImportContact;
use App\Form\Type\TransfertContact;
use App\Repository\CompanyContactRepository;
use App\Repository\CompanyRepository;
use App\Repository\TempCompanyContactRepository;
use App\Repository\UserRepository;
use App\Service\SecurityChecker;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
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
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CompanyContactCrudController extends BaseCrudController
{
    public function __construct(
        private AdminUrlGenerator $adminUrlGenerator,
        protected SecurityChecker $securityChecker,
        private CompanyContactRepository $companyContactRepository,
        private TempCompanyContactRepository $tempCompanyContactRepository,
        private CompanyRepository $companyRepository,
        private RequestStack $requestStack
    ) {
        parent::__construct($securityChecker);
    }

    public static function getEntityFqcn(): string
    {
        return CompanyContact::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud->setEntityLabelInPlural('Contacts')
            ->setEntityLabelInSingular('Contact')
            ->showEntityActionsInlined(true)
            // ->setSearchFields(['firstname', 'lastname', 'company.name', 'email', 'phone', 'gsm', 'note', 'lang', 'company.vat_number']);
            ->setSearchFields(null)

            ->setDefaultSort(['fullname' => 'ASC']);

        return parent::configureCrud($crud);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(AddedByFilter::new('added_by')->setLabel('Commercial'))
        ;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $searchDto = new SearchDto($searchDto->getRequest(), $searchDto->getSearchableProperties(), '"' . $searchDto->getQuery() . '"', $searchDto->getSort(), $searchDto->getSort(), $searchDto->getAppliedFilters());

        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        if (null === $queryBuilder->getParameter('added_by')) {
            $queryBuilder->orWhere('CONCAT(entity.firstname, \' \', entity.lastname) LIKE :query_for_text_1');
        }

        return $queryBuilder;
    }

    public function configureFields(string $pageName): iterable
    {
        $panel1 = FormField::addColumn(4, 'Société');
        $panel2 = FormField::addColumn(4, 'Contact');
        $panel6 = FormField::addColumn(4, 'Adresse');
        $panel7 = FormField::addColumn(4, 'Autres');
        $company = TextField::new('company')->setRequired(true)->setLabel('Société');
        $companyStreet = TextField::new('company.street')->setLabel('Rue')->setColumns(12)->setRequired(false);
        $companyPc = TextField::new('company.pc')->setLabel('Code postal')->setRequired(false);
        $companyCity = TextField::new('company.city')->setLabel('Ville')->setColumns(12)->setRequired(false);
        $companyCountry = CountryField::new('company.country')->setLabel('Pays')->setRequired(false);
        $companyVat = TextField::new('company.vat_number')->setLabel('Numéro de TVA')->setRequired(false);
        $companyVatNa = BooleanField::new('company.vat_na')->setLabel('Non assujetti')->setRequired(false);
        $firstname = TextField::new('firstname')->setLabel('Prénom')->setRequired(false)->setColumns(12);
        $lastname = TextField::new('lastname')->setLabel('Nom')->setRequired(false)->setColumns(12);
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
            'Sponsoring' => ['Football' => 'sponsoring_football', 'Hockey' => 'sponsoring_hockey', 'Athlètes' => 'sponsoring_athletes', 'Led Boarding' => 'sponsoring_led_boarding', 'E-Sports' => 'esports'],
        ])->allowMultipleChoices(true)->setColumns(12)->setLabel('Intérêts')->setRequired(false);

        $fonction = TextField::new('function')->setLabel('Fonction')->setColumns(12);

        $gsm = TelephoneField::new('gsm')->setLabel('Gsm')->setColumns(12);
        $note = TextEditorField::new('note')->setLabel('Note')->setColumns(12);
        $noteView = TextField::new('note')->setLabel('Note')->renderAsHtml();

        $panel3 = FormField::addColumn(4, 'Facturation');

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

        $companyEntity = AssociationField::new('company')->setColumns(12)->setRequired(false)->setFormTypeOption('query_builder', function (CompanyRepository $companyRepository) {
            return $companyRepository->createQueryBuilder('c')
                ->orderBy('c.name', 'ASC');
        });

        $userName = TextField::new('added_by.fullName')->setLabel('Commercial');
        $userNameListing = TextField::new('added_by.fullNameMinified')->setLabel('Sales');

        $panel4 = FormField::addColumn(6, 'To do');
        $panel5 = FormField::addColumn(6, 'Notes');
        $items = AssociationField::new('todos')->setTemplatePath('admin/contact/crud.detail.todos.html.twig')->setLabel(false)->setColumns(6);

        $greeting = TextField::new('greeting')->setLabel('Formule de politesse')->setColumns(12);
        $sex = ChoiceField::new('sex')->setLabel('Genre')->setChoices(
            ['Homme' => 'M', 'Femme' => 'F', 'Non binaire' => 'U']
        )->setColumns(12);

        /*
        $notes = CollectionField::new('notes')
            ->setLabel('Notes')
            ->setColumns(12)
            ->allowAdd(true)
            ->setEntryIsComplex()
            ->useEntryCrudForm(CompanyContactNoteCrudController::class)->setColumns(6);
*/
        $notesTxt = AssociationField::new('notes')
            ->setTemplatePath('admin/contact/crud.detail.notes.html.twig')
            ->setLabel(false)
            ->setColumns(6);

        $mailing = BooleanField::new('mailing');

        

        $response = match ($pageName) {
            Crud::PAGE_EDIT => [

                $panel2, $firstname, $lastname, $lang, $sex, $email, $phone, $gsm,
                $panel6, $userStreet, $userPc, $userCity, $userCountry,
                $panel7, $companyEntity, $mailing, $fonction, $interest, $user, $greeting],
            Crud::PAGE_NEW => [
                $panel2, $firstname, $lastname, $lang, $sex, $email, $phone, $gsm,
                $panel6, $userStreet, $userPc, $userCity, $userCountry->setFormTypeOption('preferred_choices', ['BE']),
                $panel7, $mailing, $fonction, $interest,],
            Crud::PAGE_DETAIL => [$panel1, $company, $companyVat, $companyVatNa, $companyStreet, $companyPc, $companyCity, $companyCountry, $panel3, $billingstreet, $billingPc, $billingcity, $billingcountry, $panel2, $fullname, $fonction, $lang, $email, $phone, $gsm, $userStreet, $userPc, $userCity, $userCountry, $interest, $userName, $noteView, $panel4, $items, $panel5, $notesTxt],
            Crud::PAGE_INDEX => [$company, $companyVat, $fullname, $langListing, $email, $phone, $gsm, $userNameListing, $note],
            default => [$company, $firstname, $lastname, $lang, $email, $phone],
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

        $createNote = Action::new('notes', 'Créer une Note')
            ->linkToCrudAction('createNote');

        $export = Action::new('export', 'Exporter mes contacts')
            ->linkToCrudAction('export')->createAsGlobalAction();


        $import = Action::new('import', 'Importer mes contacts')
            ->linkToCrudAction('import')->createAsGlobalAction();
        /*
                $search = Action::new('search', 'Rechercher un contact')
                    ->linkToCrudAction('search')->createAsGlobalAction();
        */

        $retour = Action::new('backCompany', 'Retour à la fiche société')
            ->linkToCrudAction('backtocompany');

        $actions = parent::configureActions($actions);
        $actions
            ->add(Crud::PAGE_DETAIL, $transfert)
            ->add(Crud::PAGE_DETAIL, $createTodo)
            ->add(Crud::PAGE_DETAIL, $createNote)
            ->add(Crud::PAGE_DETAIL, $retour)
            ->setPermission(Action::NEW, 'ROLE_COMMERCIAL')
            ->setPermission(Action::EDIT, 'ROLE_COMMERCIAL')
            ->setPermission(Action::DELETE, 'ROLE_COMMERCIAL')
            ->setPermission(Action::DETAIL, 'ROLE_COMMERCIAL')
            ->setPermission(Action::INDEX, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_RETURN, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_ADD_ANOTHER, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_CONTINUE, 'ROLE_COMMERCIAL')
            ->setPermission('backCompany', 'ROLE_COMMERCIAL')
            ->remove(crud::PAGE_DETAIL, Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fa fa-eye')->setLabel(false)->setHtmlAttributes(['title' => 'Consulter']);
            })
            ->add(Crud::PAGE_INDEX, $export)
            ->add(Crud::PAGE_INDEX, $import)
          //  ->add(Crud::PAGE_INDEX, $search)
            ->add(Crud::PAGE_DETAIL, Action::EDIT)
            ->update(Crud::PAGE_INDEX, 'export', function (Action $action) {
                return $action->setIcon('fa fa-file-export')->setHtmlAttributes(['title' => 'Exporter']);
            })
            ->update(Crud::PAGE_INDEX, 'import', function (Action $action) {
                return $action->setIcon('fa fa-file-import')->setHtmlAttributes(['title' => 'Importer']);
            })
           /* ->update(Crud::PAGE_INDEX, 'search', function (Action $action) {
                return $action->setIcon('fa-solid fa-magnifying-glass')->setHtmlAttributes(['title' => 'Exporter']);
            })*/
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->displayIf(function (CompanyContact $entity) {
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
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->displayIf(function (CompanyContact $entity) {
                    if ($entity->getSales()->count() > 0) {
                        return false;
                    }
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
            })
            ->update(Crud::PAGE_DETAIL, Action::EDIT, function (Action $action) {
                return $action->displayIf(function (CompanyContact $entity) {
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
            })
            ->update(Crud::PAGE_DETAIL, Action::DELETE, function (Action $action) {
                return $action->displayIf(function (CompanyContact $entity) {
                    if ($entity->getSales()->count() > 0) {
                        return false;
                    }
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
            })
            ->reorder(Crud::PAGE_DETAIL, [Action::EDIT, 'todo', 'notes', 'transfert', Action::DELETE, 'backCompany'])
        ;

        return $actions;
    }

    /**
     * @param CompanyContact $entityInstance
     */
    public function persistEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        if ('F' === $entityInstance->getSex() && 'fr' === $entityInstance->getLang()) {
            $entityInstance->setGreeting('Chère Madame');
        }

        if ('F' === $entityInstance->getSex() && 'nl' === $entityInstance->getLang()) {
            $entityInstance->setGreeting('Geachte Mevrouw');
        }

        if ('M' === $entityInstance->getSex() && 'fr' === $entityInstance->getLang()) {
            $entityInstance->setGreeting('Cher Monsieur');
        }

        if ('M' === $entityInstance->getSex() && 'nl' === $entityInstance->getLang()) {
            $entityInstance->setGreeting('Geachte Heer');
        }
        /** @var User $user */
        $user = $this->getUser();
        $entityInstance->setAddedBy($user);

        $company = $this->companyRepository->find($this->requestStack->getCurrentRequest()->get('company_id'));

        $entityInstance->setCompany($company);
        $entityInstance->setUpdatedDt(new \DateTime());

        parent::persistEntity($entityManager, $entityInstance); // TODO: Change the autogenerated stub
    }

    /**
     * @param CompanyContact $entityInstance
     */
    public function updateEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        if ('F' === $entityInstance->getSex() && 'fr' === $entityInstance->getLang() && empty($entityInstance->getGreeting())) {
            $entityInstance->setGreeting('Chère Madame');
        }

        if ('F' === $entityInstance->getSex() && 'nl' === $entityInstance->getLang() && empty($entityInstance->getGreeting())) {
            $entityInstance->setGreeting('Geachte Mevrouw');
        }

        if ('M' === $entityInstance->getSex() && 'fr' === $entityInstance->getLang() && empty($entityInstance->getGreeting())) {
            $entityInstance->setGreeting('Cher Monsieur');
        }

        if ('M' === $entityInstance->getSex() && 'nl' === $entityInstance->getLang() && empty($entityInstance->getGreeting())) {
            $entityInstance->setGreeting('Geachte Heer');
        }
        $entityInstance->setUpdatedDt(new \DateTime());
        parent::updateEntity($entityManager, $entityInstance); // TODO: Change the autogenerated stub
    }

    public function transfertContact(AdminContext $context): RedirectResponse|Response
    {
        $form = $this->createForm(TransfertContact::class, $context->getEntity()->getInstance());

        $form->handleRequest($context->getRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CompanyContact $data */
            $data = $form->getData();
            $data->setUpdatedDt(new \DateTime());
            $this->companyContactRepository->save($data, true);

            $url = $context->getReferrer();
            if (empty($url)) {
                $url = $this->adminUrlGenerator->setAction(Action::DETAIL)->generateUrl();
            }

            return $this->redirect($url);
        }

        return $this->render('admin/contact/action_transfert.html.twig', [
            'contact' => $context->getEntity()->getInstance(),
            'form' => $form,
            'referrer' => $context->getReferrer(),
        ]);
    }

    public function import(AdminContext $context): RedirectResponse|Response
    {
        $form = $this->createForm(ImportContact::class, $context->getEntity()->getInstance());

        $form->handleRequest($context->getRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $brochureFile */
            $brochureFile = $form->get('file')->getData();
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($brochureFile->getRealPath());

            $dataArray = $spreadsheet->getActiveSheet()->toArray();

            $imported = 0;
            $imported_failed = 0;
            $imported_failed_data = [];

            foreach ($dataArray as $row) {
                $contact = $this->companyContactRepository->findBy(['email' => $row[0]]);
                $tempContact = $this->tempCompanyContactRepository->findBy(['email' => $row[0]]);

                if (empty($contact) && empty($tempContact)) {

                    if (filter_var($row[0], FILTER_VALIDATE_EMAIL)) {
                        $cc = new CompanyContact();
                        $cc->setEmail($row[0]);

                        if (!empty($row[1]) && strlen($row[1]) === 2) {
                            $cc->setLang($row[1]);
                        } else {
                            $cc->setLang('fr');
                        }
                        $cc->setAddedBy($this->getUser());
                        $cc->setUpdatedDt(new \DateTime('now'));

                        $this->companyContactRepository->save($cc, true);
                        $imported++;
                    } else {
                        $imported_failed++;
                        $imported_failed_data[] = $row[0];
                    }


                } else {
                    $imported_failed++;
                    $imported_failed_data[] = $row[0];
                }


            }

            return $this->render('admin/contact/import_report.html.twig', [
                'imported'  => $imported,
                'imported_failed' => $imported_failed,
                'imported_failed_data' => $imported_failed_data,
            ]);


        }



        return $this->render('admin/contact/import.html.twig', [
            'form' => $form,
        ]);
    }
    public function createTodo(AdminContext $context): RedirectResponse|Response
    {
        return $this->redirect($this->adminUrlGenerator->setController(TodoCrudController::class)->setAction(Crud::PAGE_NEW)->setEntityId(null)->set('client_id', $context->getEntity()->getInstance()->getId())->generateUrl());
    }

    public function backtocompany(AdminContext $context): RedirectResponse|Response
    {
        return $this->redirect($this->adminUrlGenerator->setController(CompanyCrudController::class)->setAction(Crud::PAGE_DETAIL)->setEntityId($context->getEntity()->getInstance()->getCompany()->getId())->generateUrl());
    }

    public function createNote(AdminContext $context): RedirectResponse|Response
    {
        return $this->redirect($this->adminUrlGenerator->setController(CompanyContactNoteCrudController::class)->setAction(Crud::PAGE_NEW)->setEntityId(null)->set('client_id', $context->getEntity()->getInstance()->getId())->generateUrl());
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
            $activeWorksheet->setCellValue('A' . $row,  !empty($contact->getCompany()) ? $contact->getCompany()->getName() : '');
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

    public function index(AdminContext $context): Response
    {
        return $this->render('admin/contact/search.html.twig', [
        ]);
    }
}
