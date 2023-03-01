<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ProductDivers;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
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
            ->showEntityActionsInlined(true);

        return parent::configureCrud($crud);
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

    public function configureFields(string $pageName): iterable
    {
        $projectName = TextField::new('project.name')->setLabel('Nom du projet');
        $project = AssociationField::new('project')->setLabel('Projet')->setRequired(true);
        $name = TextField::new('name')->setLabel('Nom du produit')->setRequired(true);
        $percentVr = PercentField::new('percent_vr')->setLabel('Commission Verhulst')->setPermission('ROLE_ENCODE')->setStoredAsFractional(false)->setNumDecimals(2)->setRequired(true);
        $percentDefaultFreelance = PercentField::new('percent_freelance')->setLabel('Commission Freelance')->setPermission('ROLE_ENCODE')->setStoredAsFractional(false)->setNumDecimals(2)->setRequired(true);
        $percentDefaultSalarie = PercentField::new('percent_salarie')->setLabel('Commission Salarié')->setPermission('ROLE_ENCODE')->setStoredAsFractional(false)->setNumDecimals(2)->setRequired(true);
        $image = ImageField::new('doc')->setBasePath('files/products')->setUploadDir('../../shared/public/files/products')->setUploadedFileNamePattern('[slug]-[timestamp]-[randomhash].[extension]')->setLabel('Document (PDF)');;
        $imageDwonload = TextField::new('download_url')->renderAsHtml()->setLabel('Document (PDF)');

        if ($this->isGranted('ROLE_ADMIN')) {
            switch ($pageName) {
                case Crud::PAGE_DETAIL:
                case Crud::PAGE_INDEX:
                    $response = [$projectName, $name, $percentVr, $imageDwonload];
                    break;
                case Crud::PAGE_NEW:
                    $response = [$name, $percentVr, $percentDefaultFreelance, $percentDefaultSalarie, $image];
                    break;
                case Crud::PAGE_EDIT:
                    $response = [$name, $percentVr, $image];
                    break;
                default:
                    $response = [$name, $percentVr];
            }
        } else {
            switch ($pageName) {
                case Crud::PAGE_DETAIL:
                case Crud::PAGE_INDEX:
                    $response = [$projectName, $name, $percentVr];
                    break;
                case Crud::PAGE_NEW:
                case Crud::PAGE_EDIT:
                    $response = [$project, $name, $percentVr, $image];
                    break;
                default:
                    $response = [$name, $percentVr];
            }
        }

        return $response;
    }
}
