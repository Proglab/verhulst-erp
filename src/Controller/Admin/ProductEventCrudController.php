<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ProductEvent;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\PercentField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProductEventCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProductEvent::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);
        return $actions
            ->setPermission(Action::NEW, 'ROLE_COMMERCIAL')
            ->setPermission(Action::EDIT, 'ROLE_COMMERCIAL')
            ->setPermission(Action::DETAIL, 'ROLE_COMMERCIAL')
            ->setPermission(Action::INDEX, 'ROLE_COMMERCIAL')
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
            ->setPermission(Action::SAVE_AND_RETURN, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_ADD_ANOTHER, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_CONTINUE, 'ROLE_COMMERCIAL')
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud->setEntityLabelInPlural('Events')
            ->setEntityLabelInSingular('Event')
            ->showEntityActionsInlined(true)
            ->overrideTemplate('crud/index', 'admin/products/crud/index.html.twig')
            ->setDefaultSort(['project.name' => 'ASC']);

        return parent::configureCrud($crud);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('date_begin')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $project = AssociationField::new('project')->setRequired(true);
        $projectName = TextField::new('project.name')->setLabel('Nom du projet');
        $name = TextField::new('name')->setLabel('Nom du produit')->setRequired(true);
        $dateBegin = DateField::new('date_begin')->setRequired(true)->setFormat('dd/MM/yy')->setLabel('Date début');
        $dateEnd = DateField::new('date_end')->setRequired(true)->setFormat('dd/MM/yy')->setLabel('Date fin');
        $percentVr = PercentField::new('percent_vr')
            ->setLabel('Commission Verhulst')
            ->setPermission('ROLE_ADMIN')
            ->setNumDecimals(2)
            ->setStoredAsFractional(false)
            ->setRequired(true);
        $percentVrListing = PercentField::new('percent_vr')
            ->setLabel('%V')
            ->setPermission('ROLE_ADMIN')
            ->setNumDecimals(2)
            ->setStoredAsFractional(false)
            ->setRequired(true);
        $percentFreelance = PercentField::new('percent_freelance')
            ->setLabel('Commission Freelance')
            ->setPermission('ROLE_ADMIN')
            ->setNumDecimals(2)
            ->setStoredAsFractional(false)
            ->setRequired(true);
        $percentFreelanceListing = PercentField::new('percent_freelance')
            ->setLabel('%Freelance')
            ->setPermission('ROLE_ADMIN')
            ->setNumDecimals(2)
            ->setStoredAsFractional(false)
            ->setRequired(true);

        $percentSalarie = PercentField::new('percent_salarie')
            ->setLabel('Commission Salarié')
            ->setPermission('ROLE_ADMIN')
            ->setNumDecimals(2)
            ->setStoredAsFractional(false)
            ->setRequired(true);
        $percentSalarieListing = PercentField::new('percent_salarie')
            ->setLabel('%Salarié')
            ->setPermission('ROLE_ADMIN')
            ->setNumDecimals(2)
            ->setStoredAsFractional(false)
            ->setRequired(true);

        $percentTv = PercentField::new('percent_tv')
            ->setLabel('Commission Tv')
            ->setPermission('ROLE_ADMIN')
            ->setNumDecimals(2)
            ->setStoredAsFractional(false)
            ->setRequired(true);

        $percentTvListing = PercentField::new('percent_tv')
            ->setLabel('%Tv')
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

        $image = ImageField::new('doc')->setBasePath($this->getParameter('files.products.base_path'))->setUploadDir($this->getParameter('files.products.upload_dir'))->setUploadedFileNamePattern('[slug]-[timestamp]-[randomhash].[extension]')->setLabel('Document (PDF)');
        $imageDwonload = TextField::new('download_url')->renderAsHtml()->setLabel('Doc (PDF)');

        if ($this->isGranted('ROLE_ADMIN')) {
            switch ($pageName) {
                case Crud::PAGE_DETAIL:
                case Crud::PAGE_INDEX:
                    $response = [$name, $dateBegin, $dateEnd, $percentVrListing, $percentFreelanceListing, $percentSalarieListing, $percentTvListing, $imageDwonload];
                    break;
                case Crud::PAGE_NEW:
                    $response = [$name, $dateBegin, $dateEnd, $percentVr, $percentFreelance, $percentSalarie, $percentTv, $image];
                    break;
                case Crud::PAGE_EDIT:
                    $response = [$name, $dateBegin, $dateEnd, $percentVr, $percentFreelanceHidden, $percentSalarieHidden, $percentTvHidden, $image];
                    break;
                default:
                    $response = [$name, $dateBegin, $dateEnd, $percentVr, $percentFreelance, $percentSalarie, $percentTv];
            }
        } else {
            switch ($pageName) {
                case Crud::PAGE_DETAIL:
                case Crud::PAGE_INDEX:
                    $response = [$projectName, $name, $dateBegin, $dateEnd, $percentVr, $imageDwonload];
                    break;
                case Crud::PAGE_NEW:
                case Crud::PAGE_EDIT:
                    $response = [$project, $name, $dateBegin, $dateEnd, $percentVr, $image];
                    break;
                default:
                    $response = [$name, $dateBegin, $dateEnd, $percentVr];
            }
        }

        return $response;
    }
}
