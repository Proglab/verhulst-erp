<?php

declare(strict_types=1);

namespace App\Twig\Components\Budget\Invoice;

use App\Controller\Admin\Budget\DashboardController;
use App\Controller\Admin\Budget\InvoiceToValidCrudController;
use App\Entity\Budget\Invoice;
use App\Form\Type\BudgetInvoiceEditFormType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('edit_invoice_component', template: 'components/budget/invoice/edit.html.twig')]
class EditInvoiceComponent extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public ?Invoice $invoice = null;

    public function __construct(private AdminUrlGenerator $adminUrlGenerator)
    {
    }

    #[LiveAction]
    public function save(EntityManagerInterface $entityManager)
    {
        // Submit the form! If validation fails, an exception is thrown
        // and the component is automatically re-rendered with the errors
        $this->submitForm();

        /** @var Invoice $post */
        $post = $this->getForm()->getData();
        if (null === $post->getEvent()) {
            $post->setSupplier(null);
        }

        if (null === $post->getSupplier()) {
            $post->resetProduct();
        }

        $entityManager->persist($post);
        $entityManager->flush();

        return $this->redirect(
            $this->adminUrlGenerator
                ->setController(InvoiceToValidCrudController::class)
                ->setAction(Action::INDEX)
                ->setEntityId(null)
            ->setDashboard(DashboardController::class)
        );
    }

    protected function instantiateForm(): FormInterface
    {
        // we can extend AbstractController to get the normal shortcuts
        return $this->createForm(BudgetInvoiceEditFormType::class, $this->invoice);
    }
}
