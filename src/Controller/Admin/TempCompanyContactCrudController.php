<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\Filter\AddedByFilter;
use App\Entity\Company;
use App\Entity\CompanyContact;
use App\Entity\TempCompanyContact;
use App\Entity\User;
use App\Form\Type\TempTransfertContact;
use App\Repository\CompanyContactRepository;
use App\Repository\CompanyRepository;
use App\Repository\TempCompanyContactRepository;
use App\Service\SecurityChecker;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\LanguageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TempCompanyContactCrudController extends BaseCrudController
{
    public function __construct(private AdminUrlGenerator $adminUrlGenerator, protected SecurityChecker $securityChecker, private TempCompanyContactRepository $companyContactRepository, private ValidatorInterface $validator, private EntityManagerInterface $entityManager)
    {
        parent::__construct($securityChecker);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(AddedByFilter::new('added_by')->setLabel('Commercial'))
        ;
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
            ->setPaginatorPageSize(50);

        return parent::configureCrud($crud);
    }

    public function configureFields(string $pageName): iterable
    {
        $panel1 = FormField::addColumn(6, 'Société')->setCustomOption('cols', 1);
        $panel2 = FormField::addColumn(6, 'Contact')->setCustomOption('cols', 2);
        $company = TextField::new('company.name')->setRequired(true)->setLabel('Société');
        $companyStreet = TextField::new('company.street')->setLabel('Rue')->setColumns(12)->setRequired(false);
        $companyPc = TextField::new('company.pc')->setLabel('Code postal')->setRequired(false);
        $companyCity = TextField::new('company.city')->setLabel('Ville')->setColumns(12)->setRequired(false);
        $companyCountry = CountryField::new('company.country')->setLabel('Pays')->setRequired(false);
        $companyVat = TextField::new('company.vat_number')->setLabel('Numéro de TVA')->setHelp('Mettre au format Européen (BE0760577097)')->setRequired(false);
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

        $oldDB = EmailField::new('company.address')->setRequired(true)->setLabel('Ancienne DB - Adresse')->setDisabled(true)->setColumns(12);

        $fonction = TextField::new('function')->setLabel('Fonction')->setColumns(12);

        $gsm = TelephoneField::new('gsm')->setLabel('Gsm')->setColumns(12)->setHelp('Mettre au format international (+32499163111) ');

        $user = TextField::new('added_by')->setColumns(12)->setRequired(false)
            ->setLabel('Commercial')->setDisabled(true);

        $userName = TextField::new('added_by.fullName')->setLabel('Commercial');
        $userNameListing = TextField::new('added_by.fullNameMinified')->setLabel('Sales');

        $response = match ($pageName) {
            Crud::PAGE_EDIT, Crud::PAGE_DETAIL => [$panel1, $company, $companyVat, $companyStreet, $companyPc, $companyCity, $companyCountry, $oldDB, $panel2, $firstname, $lastname, $fonction, $lang, $email, $phone, $gsm, $userStreet, $userPc, $userCity, $userCountry, $user],
            Crud::PAGE_INDEX => [$company, $companyVat, $fullname, $langListing, $email, $phone, $gsm, $userNameListing],
            default => [$company, $firstname, $lastname, $lang, $email, $phone],
        };

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

                if ($this->isGranted('ROLE_ADMIN')) {
                    return true;
                }

                return $entity->getAddedBy()->getUserIdentifier() === $this->getUser()->getUserIdentifier();
            });

        $validation = Action::new('validate', 'Valider le contact')
            ->linkToCrudAction('validerContact')
            ->displayIf(function (TempCompanyContact $entity) {
                if (empty($entity->getAddedBy())) {
                    return true;
                }

                if ($this->isGranted('ROLE_ADMIN')) {
                    return true;
                }

                return $entity->getAddedBy()->getUserIdentifier() === $this->getUser()->getUserIdentifier();
            });

        $actions = parent::configureActions($actions);
        $actions
            ->add(Crud::PAGE_DETAIL, $transfert)
            ->add(Crud::PAGE_DETAIL, $validation)
            ->add(Crud::PAGE_INDEX, $validation)
            ->setPermission(Action::EDIT, 'ROLE_COMMERCIAL')
            ->setPermission(Action::DELETE, 'ROLE_COMMERCIAL')
            ->setPermission(Action::DETAIL, 'ROLE_COMMERCIAL')
            ->setPermission(Action::INDEX, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_RETURN, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_ADD_ANOTHER, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_CONTINUE, 'ROLE_COMMERCIAL')
            ->setPermission('validate', 'ROLE_COMMERCIAL')
            ->update(Crud::PAGE_DETAIL, Action::DELETE,
                function (Action $action) {
                    return $action->displayIf(function (TempCompanyContact $entity) {
                        if (empty($entity->getAddedBy())) {
                            return true;
                        }

                        if ($this->isGranted('ROLE_ADMIN')) {
                            return true;
                        }

                        return $entity->getAddedBy()->getUserIdentifier() === $this->getUser()->getUserIdentifier();
                    });
                }
            )
            ->update(Crud::PAGE_DETAIL, Action::EDIT,
                function (Action $action) {
                    return $action->displayIf(function (TempCompanyContact $entity) {
                        if (empty($entity->getAddedBy())) {
                            return true;
                        }

                        if ($this->isGranted('ROLE_ADMIN')) {
                            return true;
                        }

                        return $entity->getAddedBy()->getUserIdentifier() === $this->getUser()->getUserIdentifier();
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
            ->update(Crud::PAGE_INDEX, 'validate', function (Action $action) {
                return $action->setIcon('fa fa-user-check')->setLabel(false)->setHtmlAttributes(['title' => 'Valider']);
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

    public function transfertContact(AdminContext $context): Response
    {
        $form = $this->createForm(TempTransfertContact::class, $context->getEntity()->getInstance());

        $form->handleRequest($context->getRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            $this->companyContactRepository->save($form->getData(), true);

            return $this->redirect($context->getReferrer());
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            /** @var TempCompanyContact $client */
            $client = $form->getData();
            if (!empty($client->getAddedBy())) {
                $this->companyContactRepository->save($form->getData(), true);

                return $this->redirect($context->getReferrer());
            }
        }

        return $this->render('admin/contact/action_transfert.html.twig', [
            'contact' => $context->getEntity()->getInstance(),
            'form' => $form,
            'errors' => $form->getErrors(),
            'referrer' => $context->getReferrer(),
        ]);
    }

    public function validerContact(AdminContext $context): RedirectResponse
    {
        /** @var TempCompanyContact $contact */
        $contact = $context->getEntity()->getInstance();

        if (!empty($contact->getGsm())) {
            $gsm = str_replace(' ', '', $contact->getGsm());
            $gsm = str_replace('/', '', $gsm);
            $gsm = str_replace('.', '', $gsm);
            $contact->setGsm($gsm);
        }

        if (!empty($contact->getPhone())) {
            $phone = str_replace(' ', '', $contact->getPhone());
            $phone = str_replace('/', '', $phone);
            $phone = str_replace('.', '', $phone);
            $contact->setPhone($phone);
        }

        $errors = $this->validator->validate($contact);
        if (\count($errors) > 0) {
            foreach ($errors as $error) {
                $this->addFlash('danger', $error->getPropertyPath() . ' : ' . $error->getMessage());

                return new RedirectResponse($context->getReferrer());
            }
        }

        // valider la société
        /** @var CompanyRepository $repoCompany */
        $repoCompany = $this->entityManager->getRepository(Company::class);

        if (empty($contact->getCompany()->getVatNumber())) {
            $company = $repoCompany->findOneBy(['name' => $contact->getCompany()->getName()]);
            if (empty($company)) {
                $company = new Company();
                $company->setName($contact->getCompany()->getName());
                $company->setStreet($contact->getCompany()->getStreet());
                $company->setPc($contact->getCompany()->getPc());
                $company->setCity($contact->getCompany()->getCity());
                $company->setCountry($contact->getCompany()->getCountry());
                $company->setVatNumber($contact->getCompany()->getVatNumber());
                $repoCompany->save($company, true);
                $this->addFlash('success', $contact->getCompany()->getName() . ' importé');
            }
        } else {
            $count1 = $repoCompany->count(['vat_number' => $contact->getCompany()->getVatNumber()]);
            $count2 = $repoCompany->count(['name' => $contact->getCompany()->getName()]);
            if (0 === $count1 && 0 === $count2) {
                $company = new Company();
                $company->setName($contact->getCompany()->getName());
                $company->setStreet($contact->getCompany()->getStreet());
                $company->setPc($contact->getCompany()->getPc());
                $company->setCity($contact->getCompany()->getCity());
                $company->setCountry($contact->getCompany()->getCountry());
                $company->setVatNumber($contact->getCompany()->getVatNumber());
                $repoCompany->save($company, true);
                $this->addFlash('success', $contact->getCompany()->getName() . ' importé');
            } else {
                $company = $repoCompany->findOneBy(['vat_number' => $contact->getCompany()->getVatNumber()]);
                if (empty($company)) {
                    $company = $repoCompany->findOneBy(['name' => $contact->getCompany()->getName()]);
                }
            }
        }
        /** @var CompanyContactRepository $repoCompanyContact */
        $repoCompanyContact = $this->entityManager->getRepository(CompanyContact::class);
        $count3 = $repoCompanyContact->count(['email' => $contact->getEmail()]);
        if (empty($contact->getGsm())) {
            $count5 = 0;
        } else {
            $count5 = $repoCompanyContact->count(['gsm' => $contact->getGsm()]);
        }

        if (0 === $count3 && 0 === $count5) {
            $contactNew = new CompanyContact();
            $contactNew->setCompany($company);
            $contactNew->setStreet($contact->getStreet());
            $contactNew->setPc($contact->getPc());
            $contactNew->setCity($contact->getCity());
            $contactNew->setCountry($contact->getCountry());
            $contactNew->setEmail($contact->getEmail());
            $contactNew->setFirstname($contact->getFirstname());
            $contactNew->setLastname($contact->getLastname());
            $contactNew->setAddedBy($contact->getAddedBy());
            $contactNew->setFunction($contact->getFunction());
            $contactNew->setGsm($contact->getGsm());
            $contactNew->setPhone($contact->getPhone());
            $contactNew->setLang($contact->getLang());

            if (empty($contact->getAddedBy())) {
                /** @var ?User $user */
                $user = $this->getUser();
                $contactNew->setAddedBy($user);
            } else {
                $contactNew->setAddedBy($contact->getAddedBy());
            }

            $repoCompanyContact->save($contactNew, true);

            /** @var TempCompanyContactRepository $repo */
            $repo = $this->entityManager->getRepository(TempCompanyContact::class);
            $repo->remove($contact, true);

            $this->addFlash('success', 'Le contact ' . $contactNew->getFullName() . ' a bien été importé');

            return new RedirectResponse(
                $this->adminUrlGenerator->setController(self::class)
                    ->setAction(Crud::PAGE_INDEX)
                    ->setEntityId(null)
                    ->generateUrl());
        }
        if ($count3 > 0) {
            $this->addFlash('danger', 'Le mail <b>' . $contact->getEmail() . '</b> existe déjà');
        }
        if ($count5 > 0) {
            $this->addFlash('danger', 'Le gsm <b>' . $contact->getGsm() . '</b> existe déjà');
        }

        return new RedirectResponse($context->getReferrer());
    }
}
