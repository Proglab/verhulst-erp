<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ProductDivers;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\PercentField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProductDiversCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProductDivers::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud->setEntityLabelInPlural('Divers')
            ->setEntityLabelInSingular('Divers')
            ->showEntityActionsInlined(true)
            ->overrideTemplate('crud/index', 'admin/products/crud/index.html.twig')
            ->setDefaultSort(['project.name' => 'ASC']);

        return parent::configureCrud($crud);
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);

        return $actions
            ->setPermission(Action::NEW, 'CAN_ADD_PRODUCT')
            ->setPermission(Action::EDIT, 'ROLE_COMMERCIAL')
            ->setPermission(Action::DETAIL, 'ROLE_COMMERCIAL')
            ->setPermission(Action::INDEX, 'ROLE_COMMERCIAL')
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
            ->setPermission(Action::SAVE_AND_RETURN, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_ADD_ANOTHER, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_CONTINUE, 'ROLE_COMMERCIAL')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $projectName = TextField::new('project.name')->setLabel('Nom du projet');
        $project = AssociationField::new('project')->setLabel('Projet')->setRequired(true);
        $name = TextField::new('name')->setLabel('Nom du produit')->setRequired(true);
        $percentVr = PercentField::new('percent_vr')->setLabel('Commission Verhulst')->setPermission('ROLE_ENCODE')->setStoredAsFractional(false)->setNumDecimals(2)->setRequired(true);
        $percentVrListing = PercentField::new('percent_vr')->setLabel('%V')->setPermission('ROLE_ENCODE')->setStoredAsFractional(false)->setNumDecimals(2)->setRequired(true);
        $percentDefaultFreelance = PercentField::new('percent_freelance')->setLabel('Commission Freelance')->setPermission('ROLE_ENCODE')->setStoredAsFractional(false)->setNumDecimals(2)->setRequired(true);
        $percentDefaultSalarie = PercentField::new('percent_salarie')->setLabel('Commission Salarié')->setPermission('ROLE_ENCODE')->setStoredAsFractional(false)->setNumDecimals(2)->setRequired(true);
        $percentDefaultTv = PercentField::new('percent_tv')->setLabel('Commission TV')->setPermission('ROLE_ENCODE')->setStoredAsFractional(false)->setNumDecimals(2)->setRequired(true);
        $image = ImageField::new('doc')->setBasePath($this->getParameter('files.products.base_path'))->setUploadDir($this->getParameter('files.products.upload_dir'))->setUploadedFileNamePattern('[slug]-[timestamp]-[randomhash].[extension]')->setLabel('Document (PDF)');
        $imageDwonload = TextField::new('download_url')->renderAsHtml()->setLabel('Document (PDF)');
        $dateBegin = DateField::new('date_begin')->setRequired(true)->setFormat('dd/MM/yy')->setLabel('Date début');
        $dateEnd = DateField::new('date_end')->setRequired(true)->setFormat('dd/MM/yy')->setLabel('Date fin');

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

        if ($this->isGranted('ROLE_ENCODE')) {
            $response = match ($pageName) {
                Crud::PAGE_DETAIL, Crud::PAGE_INDEX => [$name, $dateBegin, $dateEnd, $percentVrListing, $imageDwonload],
                Crud::PAGE_NEW => [$name, $dateBegin, $dateEnd, $percentVr, $percentDefaultFreelance, $percentDefaultSalarie, $percentDefaultTv, $image],
                Crud::PAGE_EDIT => [$name, $dateBegin, $dateEnd, $percentVr, $image, $percentFreelanceHidden, $percentSalarieHidden, $percentTvHidden],
                default => [$name, $dateBegin, $dateEnd, $percentVr],
            };
        } else {
            $response = match ($pageName) {
                Crud::PAGE_DETAIL, Crud::PAGE_INDEX => [$projectName, $name, $dateBegin, $dateEnd, $percentVr],
                Crud::PAGE_NEW, Crud::PAGE_EDIT => [$project, $name, $dateBegin, $dateEnd, $percentVr, $image],
                default => [$name, $dateBegin, $dateEnd, $percentVr],
            };
        }

        return $response;
    }
}
