<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Company;
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
    public function __construct(private readonly AdminUrlGenerator $adminUrlGenerator, private readonly HttpClientInterface $client)
    {
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud->setEntityLabelInPlural('Clients')
            ->setEntityLabelInSingular('Client')
        ->showEntityActionsInlined(true);

        return parent::configureCrud($crud);
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);

        Action::new('getVatInfos', false)
            ->linkToCrudAction('getVatInfos');

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
        $panel1 = FormField::addPanel()->addCssClass('col-6');
        $name = TextField::new('name')->setRequired(true)->setColumns(12)->setLabel('Nom de la société');
        $street = TextField::new('street')->setRequired(true)->setColumns(12)->setLabel('Rue');
        $pc = TextField::new('pc')->setRequired(true)->setLabel('Code postal');
        $city = TextField::new('city')->setRequired(true)->setColumns(12)->setLabel('Ville');
        $country = CountryField::new('country')->setRequired(true)->setLabel('Pays');
        $vatNew = TextField::new('vat_number', 'Numéro de TVA')->setRequired(true)->setLabel('Numéro de TVA')->addWebpackEncoreEntries('company');
        $vat = TextField::new('vat_number', 'Numéro de TVA')->setRequired(true)->setLabel('Numéro de TVA');
        $panel2 = FormField::addPanel()->addCssClass('col-6');
        $contacts = CollectionField::new('contact')->setLabel('Contacts')->allowAdd(true)->allowDelete(true)->useEntryCrudForm(CompanyContactCrudController::class)->setColumns(12)->setRequired(true);
        $note = TextEditorField::new('note')->setLabel('Note');

        switch ($pageName) {
            case Crud::PAGE_NEW:
                $response = [$panel1, $vatNew, $name, $street, $pc, $city, $country, $note, $panel2, $contacts];
                break;
            case Crud::PAGE_DETAIL:
            case Crud::PAGE_INDEX:
            case Crud::PAGE_EDIT:
                $response = [$panel1, $vat, $name, $street, $pc, $city, $country, $note, $panel2, $contacts];
            default:
                $response = [$panel1, $vat, $name, $street, $pc, $city, $country, $note, $panel2, $contacts];
        }

        return $response;
    }

    /**
     * @return RedirectResponse|KeyValueStore|Response
     */
    public function index(AdminContext $context)
    {
        $url = $this->adminUrlGenerator
            ->setController(CompanyContactCrudController::class)
            ->setAction(Action::NEW)
            ->generateUrl();

        return $this->redirect($url);
    }

    /**
     * @return KeyValueStore|Response
     */
    public function new(AdminContext $context)
    {
        $event = new BeforeCrudActionEvent($context);
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
        /** @var Company $entityInstance */
        $entityInstance = $newForm->getData();
        $context->getEntity()->setInstance($entityInstance);

        if ($newForm->isSubmitted() && $newForm->isValid()) {
            $this->processUploadedFiles($newForm);

            $event = new BeforeEntityPersistedEvent($entityInstance);
            $this->container->get('event_dispatcher')->dispatch($event);
            $entityInstance = $event->getEntityInstance();

            if (0 === $entityInstance->getContact()->count()) {
                $newForm->get('contact')->addError(new FormError('Vous devez enregistrer au moins un contact'));
            } else {
                $i = 0;
                foreach ($context->getRequest()->get('Company')['contact'] as $contact) {
                    $entityInstance->getContact()[$i]->setLang($contact['lang']);
                    if (null === $entityInstance->getContact()[$i]->getAddedBy()) {
                        $entityInstance->getContact()[$i]->setAddedBy($this->getUser());
                    }
                }
                $this->persistEntity($this->container->get('doctrine')->getManagerForClass($context->getEntity()->getFqcn()), $entityInstance);

                $this->container->get('event_dispatcher')->dispatch(new AfterEntityPersistedEvent($entityInstance));
                $context->getEntity()->setInstance($entityInstance);

                return $this->getRedirectResponseAfterSave($context, Action::NEW);
            }
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

        return $responseParameters;
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
            'https://anyapi.io/api/v1/vat/validate?apiKey=' . $_ENV['ANYAPI_KEY'] . '8&vat_number=' . $vat
        );
        $content = json_decode($response->getContent());
        $content->vat = $vat;

        if (false === $content->valid || false === $content->validFormat) {
            return new JsonResponse($content, 404);
        }

        $content->company->street = explode(',', $content->company->address)[0];
        $content->company->pc = (string) (int) explode(',', $content->company->address)[1];
        $content->company->town = trim(str_replace($content->company->pc, '', explode(',', $content->company->address)[1]));

        return new JsonResponse($content);
    }
}
