<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ProductEvent;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
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
            ->setPermission(Action::DELETE, 'ROLE_ENCODE')
            ->setPermission(Action::SAVE_AND_RETURN, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_ADD_ANOTHER, 'ROLE_COMMERCIAL')
            ->setPermission(Action::SAVE_AND_CONTINUE, 'ROLE_COMMERCIAL')
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud->setEntityLabelInPlural('Events')
            ->setEntityLabelInSingular('Event')
            ->showEntityActionsInlined(true);

        return parent::configureCrud($crud);
    }

    public function configureFields(string $pageName): iterable
    {
        $project = AssociationField::new('project');
        $projectName = TextField::new('project.name')->setLabel('Nom du projet');
        $name = TextField::new('name')->setLabel('Nom du produit');
        $date = DateField::new('date');
        $percentVr = PercentField::new('percent_vr')
            ->setLabel('Com Verhulst')
            ->setPermission('ROLE_ADMIN')
            ->setNumDecimals(2)
            ->setStoredAsFractional(false);

        $pa = MoneyField::new('pa')
            ->setStoredAsCents(false)
            ->setNumDecimals(2)
            ->setLabel('Prix achat')
            ->setCurrency('EUR');

        $percentDefaultFreelance = PercentField::new('percent_freelance')->setLabel('Com Freelance')->setPermission('ROLE_ADMIN')->setStoredAsFractional(false)->setNumDecimals(2);
        $percentDefaultSalarie = PercentField::new('percent_salarie')->setLabel('Com Salarié')->setPermission('ROLE_ADMIN')->setStoredAsFractional(false)->setNumDecimals(2);

        $image = ImageField::new('doc')->setBasePath('files/products')->setUploadDir('../../shared/public/files/products');
        $imageDwonload = TextField::new('download_url')->renderAsHtml()->setLabel('Document');

        if ($this->isGranted('ROLE_ADMIN')) {
            switch ($pageName) {
                case Crud::PAGE_DETAIL:
                case Crud::PAGE_INDEX:
                    $response = [$projectName, $name, $date, $percentVr, $pa, $imageDwonload];
                    break;
                case Crud::PAGE_NEW:
                    $response = [$name, $date, $percentVr, $percentDefaultFreelance, $percentDefaultSalarie, $pa, $image];
                    break;
                case Crud::PAGE_EDIT:
                    $response = [$name, $date, $percentVr, $pa, $image];
                    break;
                default:
                    $response = [$name, $date, $percentVr, $pa];
            }
        } else {
            switch ($pageName) {
                case Crud::PAGE_DETAIL:
                case Crud::PAGE_INDEX:
                    $response = [$projectName, $name, $date, $percentVr, $pa, $imageDwonload];
                    break;
                case Crud::PAGE_NEW:
                case Crud::PAGE_EDIT:
                    $response = [$project, $name, $date, $percentVr, $pa, $image];
                    break;
                default:
                    $response = [$name, $date, $percentVr, $pa];
            }
        }

        return $response;
    }
}
