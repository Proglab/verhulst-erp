<?php

declare(strict_types=1);

namespace App\Twig\Components\Budget;

use App\Entity\Budget\Product;
use App\Form\Type\Budget\ProductForm;
use Doctrine\ORM\EntityManagerInterface;
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
    use ComponentWithFormTrait;
    use DefaultActionTrait;
    #[LiveProp(writable: true)]
    /** @var array|null */
    public ?Product $product = null;

    #[LiveAction]
    public function save(EntityManagerInterface $entityManager): void
    {
        // Submit the form! If validation fails, an exception is thrown
        // and the component is automatically re-rendered with the errors
        $this->submitForm();

        /** @var Product $product */
        $product = $this->getForm()->getData();

       // dd($product);

        $entityManager->persist($product);
        $entityManager->flush();

        $this->addFlash('success', 'Post saved!');
    }

    protected function instantiateForm(): FormInterface
    {
        // we can extend AbstractController to get the normal shortcuts
        return $this->createForm(ProductForm::class, $this->product);
    }
}
