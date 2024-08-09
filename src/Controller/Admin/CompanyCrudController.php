<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Company;
use App\Entity\User;
use App\Service\SecurityChecker;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use EasyCorp\Bundle\EasyAdminBundle\Exception\InsufficientEntityPermissionException;
use EasyCorp\Bundle\EasyAdminBundle\Factory\EntityFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CompanyCrudController extends BaseCrudController
{
    public function __construct(private readonly AdminUrlGenerator $adminUrlGenerator, private readonly HttpClientInterface $client, protected SecurityChecker $securityChecker)
    {
        parent::__construct($securityChecker);
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud->setEntityLabelInPlural('Sociétés')
            ->setEntityLabelInSingular('Société')
            ->showEntityActionsInlined(true)
            ->overrideTemplate('crud/detail', 'admin/company/crud/detail.html.twig')
            ->setDefaultSort(['name' => 'ASC'])
        ;

        return parent::configureCrud($crud);
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);

        Action::new('getVatInfos', false)
            ->linkToCrudAction('getVatInfos');

        $createContact = Action::new('createContact', 'Créer un contact')
            ->linkToCrudAction('createContact');

        $actions
            ->setPermission('getVatInfos', 'ROLE_COMMERCIAL')
            ->setPermission('createContact', 'ROLE_COMMERCIAL')
            ->setPermission(Action::NEW, 'ROLE_COMMERCIAL')
            ->setPermission(Action::EDIT, 'ROLE_COMMERCIAL')
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
            ->setPermission(Action::DETAIL, 'ROLE_COMMERCIAL')
            ->setPermission(Action::INDEX, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_RETURN, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_ADD_ANOTHER, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_CONTINUE, 'ROLE_COMMERCIAL')
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $createContact)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fa fa-eye')->setLabel(false)->setHtmlAttributes(['title' => 'Consulter']);
            })
        ;

        return $actions;
    }

    public static function getEntityFqcn(): string
    {
        return Company::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $panel1 = FormField::addColumn(6, 'Société');
        $name = TextField::new('name')->setRequired(true)->setLabel('Nom de la société');
        $street = TextField::new('street')->setRequired(false)->setLabel('Rue');
        $pc = TextField::new('pc')->setRequired(false)->setLabel('CP');
        $city = TextField::new('city')->setRequired(false)->setLabel('Ville');
        $country = CountryField::new('country')->setRequired(false)->setLabel('Pays');
        $vatNew = TextField::new('vat_number')->setRequired(false)->setLabel('TVA')->addWebpackEncoreEntries('company');
        $companyVatNa = BooleanField::new('vat_na')->setLabel('Non assujetti')->setRequired(false);
        $vat = TextField::new('vat_number')->setRequired(false)->setLabel('TVA');
        $note = TextEditorField::new('note')->setLabel('Note globale');
        $noteTxt = TextField::new('note')->setLabel('Note')->renderAsHtml();
        $panel3 = FormField::addColumn(6, 'Facturation')->addCssClass('col-5')->renderCollapsed()->setCustomOption('cols', 1);

        $billingstreet = TextField::new('billing_street')->setLabel('Rue');
        $billingPc = TextField::new('billing_pc')->setLabel('CP');
        $billingcity = TextField::new('billing_city')->setLabel('Ville');
        $billingcountry = CountryField::new('billing_country')->setLabel('Pays');
        $billingmail = EmailField::new('billing_mail')->setLabel('Email');

        $panel4 = FormField::addColumn(4, 'Société');
        $panel5 = FormField::addColumn(4, 'Facturation')->addCssClass('col-5')->renderCollapsed()->setCustomOption('cols', 1);
        $panel6 = FormField::addColumn(4, 'Note');
        $panel7 = FormField::addColumn(6, 'Notes');
        $panel8 = FormField::addColumn(6, 'To do');

        $contacts = CollectionField::new('contact')->setLabel(false)->allowAdd(true)->allowDelete(true)->useEntryCrudForm(CompanyContactCrudController::class)->setColumns(12)->setRequired(true)->setEntryIsComplex(true);

        $response = match ($pageName) {
            Crud::PAGE_EDIT => [$panel1, $vat, $companyVatNa, $name, $street, $pc, $city, $country, $note, $panel3, $billingstreet, $billingPc, $billingcity, $billingcountry, $billingmail],
            Crud::PAGE_NEW => [$panel1, $vatNew, $companyVatNa, $name, $street, $pc, $city, $country, $note, $panel3, $billingstreet, $billingPc, $billingcity, $billingcountry, $billingmail],
            Crud::PAGE_INDEX => [$panel1, $vat, $companyVatNa, $name, $street, $pc, $city, $country, $noteTxt],
            Crud::PAGE_DETAIL => [$panel4, $vat, $companyVatNa, $name, $street, $pc, $city, $country, $panel5, $billingstreet, $billingPc, $billingcity, $billingcountry, $billingmail, $panel6, $noteTxt, $panel7, $panel8, $contacts],
            default => [$panel1, $vat, $companyVatNa, $name, $street, $pc, $city, $country, $noteTxt],
        };

        return $response;
    }

    /**
     * @return KeyValueStore|Response
     */
    public function detail(AdminContext $context)
    {
        return $this->redirect($this->generateUrl('company_details', ['company' => $context->getEntity()->getPrimaryKeyValue()]));
    }

    public function index(AdminContext $context)
    {
        return $this->redirect($this->generateUrl('company_index'));
    }

    /**
     * @return KeyValueStore|Response
     */
    public function new(AdminContext $context)
    {
        return $this->redirectToRoute('company_create');

        /*$event = new BeforeCrudActionEvent($context);
        $this->container->get('event_dispatcher')->dispatch($event);
        if ($event->isPropagationStopped()) {
            return $event->getResponse();
        }

        if (!$this->isGranted(Permission::EA_EXECUTE_ACTION, ['action' => Action::NEW, 'entity' => null])) {
            throw new ForbiddenActionException($context);
        }

        if (!$context->getEntity()->isAccessible()) {
            throw new InsufficientEntityPermissionException($context);
        }

        $context->getEntity()->setInstance($this->createEntity($context->getEntity()->getFqcn()));
        $this->container->get(EntityFactory::class)->processFields($context->getEntity(), FieldCollection::new($this->configureFields(Crud::PAGE_NEW)));
        $context->getCrud()->setFieldAssets($this->getFieldAssets($context->getEntity()->getFields()));
        $this->container->get(EntityFactory::class)->processActions($context->getEntity(), $context->getCrud()->getActionsConfig());

        $newForm = $this->createNewForm($context->getEntity(), $context->getCrud()->getNewFormOptions(), $context);
        $newForm->handleRequest($context->getRequest());

        /** @var Company $entityInstance * /
        $entityInstance = $newForm->getData();
        $context->getEntity()->setInstance($entityInstance);

        if ($newForm->isSubmitted() && $newForm->isValid()) {
            $this->processUploadedFiles($newForm);

            $event = new BeforeEntityPersistedEvent($entityInstance);
            $this->container->get('event_dispatcher')->dispatch($event);
            $entityInstance = $event->getEntityInstance();
            $this->persistEntity($this->container->get('doctrine')->getManagerForClass($context->getEntity()->getFqcn()), $entityInstance);

            $this->container->get('event_dispatcher')->dispatch(new AfterEntityPersistedEvent($entityInstance));
            $context->getEntity()->setInstance($entityInstance);

            return $this->getRedirectResponseAfterSave($context, Action::NEW);
        }

        $responseParameters = $this->configureResponseParameters(KeyValueStore::new([
            'pageName' => Crud::PAGE_NEW,
            'templateName' => 'crud/new',
            'entity' => $context->getEntity(),
            'new_form' => $newForm,
        ]));

        $event = new AfterCrudActionEvent($context, $responseParameters);
        $this->container->get('event_dispatcher')->dispatch($event);
        if ($event->isPropagationStopped()) {
            return $event->getResponse();
        }

        return $responseParameters;*/
    }

    /**
     * @return KeyValueStore|Response
     */
    public function edit(AdminContext $context)
    {
        $event = new BeforeCrudActionEvent($context);
        $this->container->get('event_dispatcher')->dispatch($event);
        if ($event->isPropagationStopped()) {
            return $event->getResponse();
        }

        if (!$this->isGranted(Permission::EA_EXECUTE_ACTION, ['action' => Action::EDIT, 'entity' => $context->getEntity()])) {
            throw new ForbiddenActionException($context);
        }

        if (!$context->getEntity()->isAccessible()) {
            throw new InsufficientEntityPermissionException($context);
        }

        $this->container->get(EntityFactory::class)->processFields($context->getEntity(), FieldCollection::new($this->configureFields(Crud::PAGE_EDIT)));
        $context->getCrud()->setFieldAssets($this->getFieldAssets($context->getEntity()->getFields()));
        $this->container->get(EntityFactory::class)->processActions($context->getEntity(), $context->getCrud()->getActionsConfig());
        $entityInstance = $context->getEntity()->getInstance();

        if ($context->getRequest()->isXmlHttpRequest()) {
            if ('PATCH' !== $context->getRequest()->getMethod()) {
                throw new MethodNotAllowedHttpException(['PATCH']);
            }

            if (!$this->isCsrfTokenValid(BooleanField::CSRF_TOKEN_NAME, $context->getRequest()->query->get('csrfToken'))) {
                if (class_exists(InvalidCsrfTokenException::class)) {
                    throw new InvalidCsrfTokenException();
                }

                return new Response('Invalid CSRF token.', 400);
            }

            $fieldName = $context->getRequest()->query->get('fieldName');
            $newValue = 'true' === mb_strtolower($context->getRequest()->query->get('newValue'));

            try {
                $event = $this->ajaxEdit($context->getEntity(), $fieldName, $newValue);
            } catch (\Exception) {
                throw new BadRequestHttpException();
            }

            if ($event->isPropagationStopped()) {
                return $event->getResponse();
            }

            return new Response($newValue ? '1' : '0');
        }

        $editForm = $this->createEditForm($context->getEntity(), $context->getCrud()->getEditFormOptions(), $context);
        $editForm->handleRequest($context->getRequest());
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->processUploadedFiles($editForm);

            $event = new BeforeEntityUpdatedEvent($entityInstance);
            $this->container->get('event_dispatcher')->dispatch($event);
            $entityInstance = $event->getEntityInstance();

            if (0 === $entityInstance->getContact()->count()) {
                $editForm->get('contact')->addError(new FormError('Vous devez enregistrer au moins un contact'));
            } else {
                $this->updateEntity($this->container->get('doctrine')->getManagerForClass($context->getEntity()->getFqcn()), $entityInstance);

                $this->container->get('event_dispatcher')->dispatch(new AfterEntityUpdatedEvent($entityInstance));

                return $this->getRedirectResponseAfterSave($context, Action::EDIT);
            }
        }

        $responseParameters = $this->configureResponseParameters(KeyValueStore::new([
            'pageName' => Crud::PAGE_EDIT,
            'templateName' => 'crud/edit',
            'edit_form' => $editForm,
            'entity' => $context->getEntity(),
        ]));

        $event = new AfterCrudActionEvent($context, $responseParameters);
        $this->container->get('event_dispatcher')->dispatch($event);
        if ($event->isPropagationStopped()) {
            return $event->getResponse();
        }

        return $responseParameters;
    }

    public function getVatInfos(AdminContext $context): JsonResponse
    {
        $vat = $context->getRequest()->get('vat');
        $vat = str_replace('.', '', $vat);
        $vat = str_replace(' ', '', $vat);

        if (empty($vat)) {
            return new JsonResponse(['vat_null'], 500);
        }

        if (11 !== \strlen($vat) && 12 !== \strlen($vat)) {
            return new JsonResponse([], 500);
        }

        if (11 === \strlen($vat)) {
            $vatCountry = substr($vat, 0, -9);
            $vatNum = substr($vat, -9, 9);
            $vat = $vatCountry . '0' . $vatNum;
        } else {
            $vatCountry = substr($vat, 0, -10);
            $vatNum = substr($vat, -10, 10);
        }

        if (!is_numeric($vatNum)) {
            return new JsonResponse([], 500);
        }

        if (2 !== \strlen($vatCountry)) {
            return new JsonResponse([], 500);
        }

        $response = $this->client->request(
            'GET',
            'http://apilayer.net/api/validate?access_key=' . $_ENV['ANYAPI_KEY'] . '&vat_number=' . $vat . '&format=1'
        );
        $content = json_decode($response->getContent());

        if (false === $content->valid || false === $content->format_valid) {
            return new JsonResponse($content, 404);
        }
        $content->vat = $vat;
        $content->company = new \stdClass();
        $content->company->name = $content->company_name;
        $content->company->street = explode("\n", $content->company_address)[0];
        $content->company->pc = (string) (int) explode("\n", $content->company_address)[1];
        $content->company->town = trim(str_replace($content->company->pc, '', explode("\n", $content->company_address)[1]));
        $content->country = new \stdClass();
        $content->country->isoCode = new \stdClass();
        $content->country->isoCode->short = $content->country_code;

        return new JsonResponse($content);
    }

    /**
     * @param Company $entityInstance
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::persistEntity($entityManager, $entityInstance); // TODO: Change the autogenerated stub
    }

    /**
     * @param Company $entityInstance
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        foreach ($entityInstance->getContact() as $contact) {
            foreach ($contact->getNotes() as $note) {
                if (null === $note->getId()) {
                    /** @var User $user */
                    $user = $this->getUser();
                    $note->setUser($user);
                    $note->setCreatedDt(new \DateTime('now'));
                }
            }
        }
        parent::updateEntity($entityManager, $entityInstance); // TODO: Change the autogenerated stub
    }

    public function createContact(AdminContext $adminContext): RedirectResponse
    {
        $url = $this->adminUrlGenerator
            ->setController(CompanyContactCrudController::class)
            ->setAction(Crud::PAGE_NEW)->set('company_id', $adminContext->getEntity()->getInstance()->getId())
            ->setEntityId(null)
            ->generateUrl();

        return $this->redirect($url);
    }

    protected function getRedirectResponseAfterSave(AdminContext $context, string $action): RedirectResponse
    {
        $url = $this->adminUrlGenerator->setDashboard(DashboardController::class)->setController(self::class)->setAction(Crud::PAGE_DETAIL)->setEntityId($context->getEntity()->getInstance()->getId())->generateUrl();

        return $this->redirect($url);
    }
}
