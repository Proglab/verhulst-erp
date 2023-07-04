<?php

namespace App\Controller\Admin;

use App\Entity\SalesBdc;
use Dompdf\Dompdf;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SalesBdcCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return SalesBdc::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud->setEntityLabelInPlural('Bons de commande')
            ->setEntityLabelInSingular('Bon de commande')
            ->showEntityActionsInlined(true);

        return parent::configureCrud($crud);
    }

    public function configureFields(string $pageName): iterable
    {
        $date = DateField::new('creationDate')->setLabel('Date de création');
        $validate = BooleanField::new('validate')->setLabel('Validation du client');
        $sales = AssociationField::new('sales')->setRequired(true)->setLabel('Ventes');
        $company = TextField::new('sales[0].contact.company')->setRequired(true)->setLabel('Société');
        $project = TextField::new('sales[0].product.project')->setRequired(true)->setLabel('Projet');
        $commercial = TextField::new('user')->setRequired(true)->setLabel('Commercial');

        switch ($pageName) {
            case Crud::PAGE_DETAIL:
                $response = [$date, $commercial, $company, $project, $sales, $validate];
                break;
            default:
                $response = [$date, $commercial, $company, $project, $sales, $validate];
        }

        return $response;
    }

    public function configureActions(Actions $actions): Actions
    {
        $generatePdf = Action::new('generatePdf', 'Générer le pdf')
            ->linkToCrudAction('generatePdf');

        $actions = parent::configureActions($actions);
        $actions
            ->add(Crud::PAGE_DETAIL, $generatePdf)
            ->add(Crud::PAGE_INDEX, $generatePdf)
            ->disable(Action::NEW)
            ->disable(Action::EDIT)
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
            ->setPermission(Action::DETAIL, 'ROLE_COMMERCIAL')
            ->setPermission(Action::INDEX, 'ROLE_COMMERCIAL')
            ->disable(Action::SAVE_AND_RETURN)
            ->disable(Action::SAVE_AND_ADD_ANOTHER)
            ->disable(Action::SAVE_AND_CONTINUE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fa fa-eye')->setLabel(false)->setHtmlAttributes(['title' => 'Consulter']);
            })
            ->update(Crud::PAGE_INDEX, 'generatePdf', function (Action $action) {
                return $action->setIcon('fa fa-file-pdf')->setLabel(false)->setHtmlAttributes(['title' => 'Générer le Bdc']);
            })
        ;

        return $actions;
    }




    public function generatePdf(AdminContext $context):void {

        $bdc = $context->getEntity()->getInstance();
        $html = $this->render('admin/pdf/bdc_fr.html.twig', [
            'logo' => 'app-logo.png',
            'bdc' => $bdc
        ]);
        $dompdf = new Dompdf(array('enable_remote' => true));
        $dompdf->getOptions()->setChroot(realpath(__DIR__.'/../../../public/'));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream("codexworld", ["Attachment" => 0]);

        exit();
    }
}
