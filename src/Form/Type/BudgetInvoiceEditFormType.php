<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Budget\Event;
use App\Entity\Budget\Product;
use App\Entity\Budget\Supplier;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;

class BudgetInvoiceEditFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder = new DynamicFormBuilder($builder);

        $builder->add('price', MoneyType::class);
        $builder->add('event', EntityType::class, [
            'class' => Event::class,
            'multiple' => false,
            'expanded' => false,
            'placeholder' => '',
            'required' => false,
        ]);

        $builder->addDependent('supplier', ['event'], function (DependentField $field, ?Event $event) {
            if (null === $event) {
                return; // field not needed
            }
            $field->add(EntityType::class, [
                'class' => Supplier::class,
                'multiple' => false,
                'expanded' => false,
                'placeholder' => '',
                'query_builder' => function (EntityRepository $er) use ($event): QueryBuilder {
                    return $er->createQueryBuilder('s')
                        ->join('s.products', 'p')
                        ->join('p.sub_category', 'subc')
                        ->join('subc.category', 'c')
                        ->join('c.budget', 'b')
                        ->andWhere('b.event = :event')
                        ->setParameter('event', $event)
                        ->orderBy('s.name', 'ASC');
                },
                'required' => false,
            ]);
        });

        $builder->addDependent('products', ['supplier', 'event'], function (DependentField $field, ?Supplier $supplier) {
            if (null === $supplier) {
                return; // field not needed
            }
            $field->add(EntityType::class, [
                'class' => Product::class,
                'multiple' => true,
                'expanded' => true,
                'query_builder' => function (EntityRepository $er) use ($supplier): QueryBuilder {
                    return $er->createQueryBuilder('p')
                        ->andWhere('p.supplier = :supplier')
                        ->setParameter('supplier', $supplier)
                    ;
                },
                'required' => false,
            ]);
        });

        $builder->addDependent('validated', ['products', 'supplier', 'event'], function (DependentField $field, ?PersistentCollection $products) {
            if (null === $products) {
                return; // field not needed
            }

            if ($products->count() <= 0) {
                return; // field not needed
            }

            $field->add(CheckboxType::class, [
                'label' => 'Validée ?',
                'required' => false,
                'value' => true,
            ]);
        });

        $builder->add('valid', SubmitType::class, [
            'label' => 'Enregistrer',
        ]);
    }
}
