<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DatesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_begin', DateType::class, [
                'label' => 'Date de début',
                'required' => true,
                'mapped' => true,
                'widget' => 'single_text',
                'html5' => false,
            ])
            ->add('date_end', DateType::class, [
                'label' => 'Date de fin',
                'required' => true,
                'mapped' => true,
                'widget' => 'single_text',
                'html5' => false,
                'label_attr' => [
                    'class' => 'mt-3',
                ],
            ])
            ->add('create_all_date', CheckboxType::class, [
                'label' => 'Créer un produit pour chaque date ?',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-check form-check-inline mt-3',
                ],
                'label_attr' => [
                    'class' => 'mt-3',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
