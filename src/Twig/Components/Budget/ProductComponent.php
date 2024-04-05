<?php
namespace App\Twig\Components\Budget;

use App\Controller\Admin\Budget\DashboardController;
use App\Controller\Admin\Budget\InvoiceToValidCrudController;
use App\Entity\Budget\Invoice;
use App\Entity\Budget\Product;
use App\Form\Type\Budget\ProductForm;
use App\Form\Type\BudgetInvoiceEditFormType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('budget_product_component', template: 'components/budget/product.html.twig')]
class ProductComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;
    #[LiveProp(writable: true)]
    /** @var array|null */
    public ?Product $product = null;

    protected function instantiateForm(): FormInterface
    {
        // we can extend AbstractController to get the normal shortcuts
        return $this->createForm(ProductForm::class, $this->product);
    }

    #[LiveAction]
    public function save(EntityManagerInterface $entityManager)
    {
        // Submit the form! If validation fails, an exception is thrown
        // and the component is automatically re-rendered with the errors
        $this->submitForm();

        /** @var Product $product */
        $product = $this->getForm()->getData();

        dd($product);



        $entityManager->persist($product);
        $entityManager->flush();

        $this->addFlash('success', 'Post saved!');
    }
}