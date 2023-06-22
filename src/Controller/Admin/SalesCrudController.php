<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Commission;
use App\Entity\Company;
use App\Entity\CompanyContact;
use App\Entity\Product;
use App\Entity\ProductPackageVip;
use App\Entity\ProductSponsoring;
use App\Entity\Project;
use App\Entity\Sales;
use App\Entity\User;
use App\Repository\CompanyRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use App\Service\SecurityChecker;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use EasyCorp\Bundle\EasyAdminBundle\Exception\InsufficientEntityPermissionException;
use EasyCorp\Bundle\EasyAdminBundle\Factory\EntityFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\HiddenField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\PercentField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class SalesCrudController extends BaseCrudController
{
    public function __construct(private EntityManagerInterface $entityManager, private AdminUrlGenerator $adminUrlGenerator, private UserRepository $userRepository, protected SecurityChecker $securityChecker)
    {
        parent::__construct($securityChecker);
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud->setEntityLabelInPlural('Ventes')
            ->setEntityLabelInSingular('Vente')
            ->showEntityActionsInlined(true)
            ->overrideTemplate('crud/detail', 'admin/crud/detail_2cols.html.twig');

        return parent::configureCrud($crud);
    }

    public function configureActions(Actions $actions): Actions
    {
        $searchClient = Action::new('searchClient', false)
            ->linkToCrudAction('searchClient');

        $searchProduct = Action::new('searchProduct', false)
            ->linkToCrudAction('searchProduct');

        $listProduct = Action::new('listProduct', false)
            ->linkToCrudAction('listProduct');

        $searchClient = Action::new('sales_by_users_list', false)
            ->linkToCrudAction('sales_by_users_list');

        $actions = parent::configureActions($actions);
        $actions
            ->setPermission('listProduct', 'ROLE_COMMERCIAL')
            ->setPermission('sales_by_users_list', 'ROLE_COMMERCIAL')
            ->setPermission(Action::NEW, 'ROLE_COMMERCIAL')
            ->setPermission(Action::EDIT, 'ROLE_COMMERCIAL')
            ->setPermission(Action::DELETE, 'ROLE_COMMERCIAL')
            ->setPermission(Action::DETAIL, 'ROLE_COMMERCIAL')
            ->setPermission(Action::INDEX, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_RETURN, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_ADD_ANOTHER, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_CONTINUE, 'ROLE_COMMERCIAL')
            ->setPermission('searchClient', 'ROLE_COMMERCIAL')
            ->setPermission('searchProduct', 'ROLE_COMMERCIAL')
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fa fa-eye');
            })
            ->add(Crud::PAGE_EDIT, Action::DELETE)
        ;

        return $actions;
    }

    public static function getEntityFqcn(): string
    {
        return Sales::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $price = MoneyField::new('price')
            ->setStoredAsCents(false)
            ->setNumDecimals(2)
            ->setRequired(true)
            ->setCurrency('EUR')->setLabel('Prix');
        $product = AssociationField::new('product')->setRequired(true);
        $contacts = AssociationField::new('contact')->setRequired(true);
        $date = DateField::new('date')->setLabel('Date de vente');
        $percent_com = PercentField::new('percent_com')
            ->setNumDecimals(2)
            ->setStoredAsFractional(false)
            ->setPermission('ROLE_ENCODE');
        $percent_vr = PercentField::new('percent_vr')
            ->setNumDecimals(2)
            ->setStoredAsFractional(false)
            ->setPermission('ROLE_ENCODE');

        $quantity = IntegerField::new('quantity')->setLabel('Quantité');
        $discount_eur = MoneyField::new('discount_eur')
            ->setStoredAsCents(false)
            ->setNumDecimals(2)
            ->setRequired(false)
            ->setCurrency('EUR')
            ->setLabel('Réduction (EUR)');
        $discount_percent = PercentField::new('discount_percent')
            ->setRequired(false)
            ->setStoredAsFractional(false)
            ->setLabel('Réduction (%)')
            ->setNumDecimals(2);
        $discount = HiddenField::new('discount');
        $discount_edit = MoneyField::new('discount')
            ->setStoredAsCents(false)
            ->setNumDecimals(2)
            ->setRequired(false)
            ->setCurrency('EUR')
            ->setLabel('Réduction (EUR)');

        $panelProduct = FormField::addPanel('Produit')->setCustomOption('cols', 1);

        $panelClient = FormField::addPanel('Client')->setCustomOption('cols', 2);

        $panelVente = FormField::addPanel('Vente')->setCustomOption('cols', 1);

        $panelContact = FormField::addPanel('Contact')->setCustomOption('cols', 2);

        $project = TextField::new('product.project')->setLabel('Projet');
        $dateBegin = DateField::new('product.date_begin')->setLabel('Du')->setFormat('dd/MM/yy');
        $dateEnd = DateField::new('product.date_end')->setLabel('Au')->setFormat('dd/MM/yy');
        $user = TextField::new('user.fullname')->setLabel('Vendeur');
        $userMail = EmailField::new('user.email')->setLabel('Mail');
        $description = TextField::new('product.description')->setLabel('Description')->renderAsHtml();

        $company = TextField::new('contact.company')->setLabel('Société');
        $companyVat = TextField::new('contact.company.vat_number')->setLabel('Tva');
        $companyStreet = TextField::new('contact.company.street')->setLabel('Rue');
        $companyPc = TextField::new('contact.company.pc')->setLabel('Code postal');
        $companyCity = TextField::new('contact.company.city')->setLabel('Ville');
        $companyCountry = CountryField::new('contact.company.country')->setLabel('Pays');
        $priceTotal = MoneyField::new('total_price')
            ->setStoredAsCents(false)
            ->setNumDecimals(2)
            ->setRequired(true)
            ->setCurrency('EUR')->setLabel('Prix Total');

        if ($this->isGranted('ROLE_ENCODE')) {
            $pa = MoneyField::new('pa')
                ->setStoredAsCents(false)
                ->setNumDecimals(2)
                ->setRequired(true)
                ->setCurrency('EUR')->setLabel('Prix d\'achat');
        } else {
            $pa = HiddenField::new('pa');
        }

        $priceMarge = MoneyField::new('marge')
            ->setStoredAsCents(false)
            ->setNumDecimals(2)
            ->setRequired(true)
            ->setCurrency('EUR')->setLabel('Prix final');
        $invoiced = BooleanField::new('invoiced')
            ->setLabel('Facturé')->setDisabled(true);
        $dateValidation = DateField::new('invoicedDt')->setLabel('Date de validation')->setFormat('dd/MM/yy HH:MM');
        $contact = TextField::new('contact.fullname')->setLabel('Nom');
        $contactTel = TelephoneField::new('contact.phone')->setLabel('Tel');
        $contactGsm = TelephoneField::new('contact.gsm')->setLabel('Gsm');
        $contactEmail = EmailField::new('contact.email')->setLabel('Mail');

        switch ($pageName) {
            case Crud::PAGE_NEW:
                $response = [$product, $contacts, $quantity, $pa, $price, $discount_eur, $discount_percent, $date, $discount];
                break;

            case Crud::PAGE_EDIT:
                $response = [$product, $contacts, $quantity, $pa, $price, $discount_edit, $percent_com, $percent_vr, $date];
                break;
            case Crud::PAGE_DETAIL:
                $response = [$panelProduct,
                    $project, $product, $dateBegin, $dateEnd, $user, $userMail, $description,
                    $panelClient,
                    $company, $companyVat, $companyStreet, $companyPc, $companyCity, $companyCountry,
                    $panelVente,
                    $pa, $price, $quantity, $priceTotal, $discount, $priceMarge, $date, $invoiced, $dateValidation,
                    $panelContact,
                    $contact, $contactTel, $contactGsm, $contactEmail];
                break;
            default:
                $response = [$product, $contacts, $quantity, $pa, $price, $discount_eur, $discount_percent, $date, $discount];
        }

        return $response;
    }

    public function createNewForm(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormInterface
    {
        /** @var Sales $instance */
        $instance = $entityDto->getInstance();
        $instance->setDate(new \DateTime());
        $entityDto->setInstance($instance);

        return parent::createNewForm($entityDto, $formOptions, $context); // TODO: Change the autogenerated stub
    }

    /**
     * @param Sales $entityInstance
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var User $user */
        $user = $this->getUser();
        $entityInstance->setUser($user);
        $entityInstance->setPercentVr($entityInstance->getProduct()->getPercentVr());

        if ($entityInstance->getProduct()->getPercentVr() > 0) {
            $entityInstance->setPa($entityInstance->getDiffCa() / $entityInstance->getQuantity());
        }

        /** @var Commission $com */
        $com = $entityManager->getRepository(Commission::class)->findOneBy(['product' => $entityInstance->getProduct(), 'user' => $this->getUser()]);

        $percent_com = 0;
        if (null !== $com) {
            $percent_com = $com->getPercentCom();
        }

        $entityInstance->setPercentCom($percent_com);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        /** @var QueryBuilder $qb */
        $qb = $this->container->get(EntityRepository::class)->createQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $qb->andWhere('entity.user = :user')
            ->setParameter('user', $this->getUser());

        return $qb;
    }

    /**
     * @return KeyValueStore|Response
     */
    public function index(AdminContext $context)
    {
        $user = $this->getUser();

        return $this->render('admin/sales/index.html.twig', [
            'user' => $user,
        ]);
    }

    public function sales_by_users_list(): Response
    {
        $users = $this->userRepository->getCommercials();

        return $this->render('admin/sales/sales_by_users_list.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * @return RedirectResponse|KeyValueStore|Response
     */
    public function new(AdminContext $context)
    {
        return $this->render('admin/sales/new.html.twig', [
            'context' => $context,
        ]);
    }

    public function searchClient(AdminContext $context): Response
    {
        $request = $context->getRequest();
        /** @var CompanyRepository $repo */
        $repo = $this->entityManager->getRepository(Company::class);
        $company = $repo->search($request->get('search'));

        return $this->render('admin/sales/ajax/search_client.html.twig', [
                'companies' => $company,
            ]
        );
    }

    public function listProduct(AdminContext $context): Response
    {
        $request = $context->getRequest();
        $contact = $this->entityManager->getRepository(CompanyContact::class)->find($request->get('contactId'));

        return $this->render('admin/sales/list_product.html.twig', [
                'contact' => $contact,
                'context' => $context,
            ]
        );
    }

    public function searchProduct(AdminContext $context): Response
    {
        $request = $context->getRequest();
        /** @var ProjectRepository $repo */
        $repo = $this->entityManager->getRepository(Project::class);
        $projects = $repo->search($request->get('search'));

        return $this->render('admin/sales/ajax/search_product.html.twig', [
                'projects' => $projects,
                'contact_id' => $request->get('contactId'),
            ]
        );
    }

    public function createSale(AdminContext $context): Response
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

        $entity = $context->getEntity()->getInstance();
        $contact = $this->entityManager->getRepository(CompanyContact::class)->find($context->getRequest()->get('contactId'));
        /** @var Product $product */
        $product = $this->entityManager->getRepository(Product::class)->find($context->getRequest()->get('productId'));
        $entity->setProduct($product);
        $entity->setContact($contact);
        $entity->setUser($this->getUser());
        if ($product instanceof ProductPackageVip || $product instanceof ProductSponsoring) {
            $entity->setPrice((string) $product->getCa());
            $entity->setPa((string) $product->getPa());
        }

        $newForm = $this->createNewForm($context->getEntity(), $context->getCrud()->getNewFormOptions(), $context);
        $newForm->handleRequest($context->getRequest());

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

            if (isset($_POST['submit'])) {
                return $this->redirect($this->adminUrlGenerator
                    ->setAction(Action::INDEX)
                    ->generateUrl());
            }

            return $this->redirect($this->adminUrlGenerator
                ->setAction('listProduct')
                ->generateUrl());
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
        if ($product instanceof ProductPackageVip || $product instanceof ProductSponsoring) {
            $quantity_available = $product->getQuantityAvailable();
        } else {
            $quantity_available = null;
        }

        $return = [
            'form' => $newForm->createView(),
            'contact' => $contact,
            'product' => $product,
        ];
        if (null !== $quantity_available) {
            $return['stock_avalaible'] = $quantity_available;
        }

        return $this->render('admin/sales/final.html.twig', $return);
    }
}
