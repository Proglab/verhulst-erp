<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ProductPackageVip;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\PercentField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ProductPackageVipCrudController extends BaseCrudController
{
    public function __construct(private AdminUrlGenerator $adminUrlGenerator)
    {
    }

    public static function getEntityFqcn(): string
    {
        return ProductPackageVip::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud->setEntityLabelInPlural('Packages Vip')
            ->setEntityLabelInSingular('Package Vip')
            ->showEntityActionsInlined(true)
            ->overrideTemplate('crud/index', 'admin/products/crud/index.html.twig')
            ->setDefaultSort(['project.name' => 'ASC']);

        return parent::configureCrud($crud);
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);

        return $actions
            ->disable(Action::NEW)
            ->setPermission(Action::EDIT, 'ROLE_ENCODE')
            ->setPermission(Action::DETAIL, 'ROLE_COMMERCIAL')
            ->setPermission(Action::INDEX, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_RETURN, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_ADD_ANOTHER, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_CONTINUE, 'ROLE_COMMERCIAL')
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $projectName = TextField::new('project.name')->setLabel('Nom du projet');
        $name = TextField::new('name')->setLabel('Nom du produit')->setRequired(true);
        $percentVr = PercentField::new('percent_vr')->setLabel('Commission Verhulst')->setPermission('ROLE_ENCODE')->setStoredAsFractional(false)->setNumDecimals(2)->setRequired(true);
        $percentVrListing = PercentField::new('percent_vr')->setLabel('%V')->setPermission('ROLE_ENCODE')->setStoredAsFractional(false)->setNumDecimals(2)->setRequired(true);
        $percentDefaultFreelance = PercentField::new('percent_freelance')->setLabel('Commission Freelance')->setPermission('ROLE_ENCODE')->setStoredAsFractional(false)->setNumDecimals(2)->setRequired(true);
        $percentDefaultSalarie = PercentField::new('percent_salarie')->setLabel('Commission Salarié')->setPermission('ROLE_ENCODE')->setStoredAsFractional(false)->setNumDecimals(2)->setRequired(true);
        $ca = MoneyField::new('ca')->setCurrency('EUR')->setStoredAsCents(false)->setNumDecimals(2)->setLabel('Prix de vente')->setRequired(true);
        $caListing = MoneyField::new('ca')->setCurrency('EUR')->setStoredAsCents(false)->setNumDecimals(2)->setLabel('PV')->setRequired(true);
        $description = TextEditorField::new('description');
        $quantityMax = IntegerField::new('quantity_max')->setLabel('Quantité max');
        $quantityMaxListing = IntegerField::new('quantity_max')->setLabel('Q max');
        $quantitySalesListing = IntegerField::new('quantity_sales')->setLabel('Q vendue');
        $quantityAvailableListing = IntegerField::new('quantity_available')->setLabel('Q disponible');
        $image = ImageField::new('doc')->setBasePath($this->getParameter('files.products.base_path'))->setUploadDir($this->getParameter('files.products.upload_dir'))->setUploadedFileNamePattern('[slug]-[timestamp]-[randomhash].[extension]')->setLabel('Document (PDF)');
        $imageDwonload = TextField::new('download_url')->renderAsHtml()->setLabel('Doc (PDF)');
        $dateBegin = DateField::new('date_begin')->setRequired(true)->setFormat('dd/MM/yy')->setLabel('Date');

        $pa = MoneyField::new('pa')->setCurrency('EUR')->setStoredAsCents(false)->setNumDecimals(2)->setLabel('Prix d\'achat')->setRequired(false)->setPermission('ROLE_ENCODE');
        $paListing = MoneyField::new('pa')->setCurrency('EUR')->setStoredAsCents(false)->setNumDecimals(2)->setLabel('PA')->setRequired(false)->setPermission('ROLE_ENCODE');

        $percentTv = PercentField::new('percent_tv')
            ->setLabel('Commission Tv')
            ->setPermission('ROLE_ADMIN')
            ->setNumDecimals(2)
            ->setStoredAsFractional(false)
            ->setRequired(true);

        $percentFreelanceHidden = PercentField::new('percent_freelance')
            ->setLabel('Commission Freelance')
            ->setPermission('ROLE_ADMIN')
            ->setNumDecimals(2)
            ->setStoredAsFractional(false)
            ->setRequired(false)
            ->setCssClass('d-none');

        $percentSalarieHidden = PercentField::new('percent_salarie')
            ->setLabel('Commission Salarié')
            ->setPermission('ROLE_ADMIN')
            ->setNumDecimals(2)
            ->setStoredAsFractional(false)
            ->setRequired(false)
            ->setCssClass('d-none');

        $percentTvHidden = PercentField::new('percent_tv')
            ->setLabel('Commission Tv')
            ->setPermission('ROLE_ADMIN')
            ->setNumDecimals(2)
            ->setStoredAsFractional(false)
            ->setRequired(false)
            ->setCssClass('d-none');

        $response = match ($pageName) {
            Crud::PAGE_DETAIL, Crud::PAGE_INDEX => [$name, $dateBegin, $percentVrListing, $paListing, $caListing, $description, $quantityMaxListing, $quantitySalesListing, $quantityAvailableListing, $imageDwonload],
            Crud::PAGE_NEW => [$name, /* $dateBegin, */ $percentVr, $percentDefaultFreelance, $percentDefaultSalarie, $percentTv, $pa, $ca, $description, $quantityMax, $image],
            Crud::PAGE_EDIT => [$name, $dateBegin, $percentVr, $pa, $ca, $description, $quantityMax, $image, $percentFreelanceHidden, $percentSalarieHidden, $percentTvHidden],
            default => [$name, /* $dateBegin, */ $percentVr, $ca, $description, $quantityMax],
        };

        return $response;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters); // TODO: Change the autogenerated stub

        return $qb->orderBy('project.name, entity.name');
    }

    public function configureAssets(Assets $assets): Assets
    {
        $assets = parent::configureAssets($assets);
        $assets->addWebpackEncoreEntry('package_vip');

        return $assets;
    }

    protected function getRedirectResponseAfterSave(AdminContext $context, string $action): RedirectResponse
    {
        return $this->redirect($this->adminUrlGenerator->setController(ProjectCrudController::class)->setAction(Action::DETAIL)->setEntityId($context->getEntity()->getInstance()->getProject()->getId())->generateUrl());
    }
}
