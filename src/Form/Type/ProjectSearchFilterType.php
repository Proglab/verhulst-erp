<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ProjectSearchFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'required' => false,
                'attr' => [
                    'data-model' => 'query',
                ],
            ])
            ->add('from', DateType::class, [
                'html5' => true,
                'widget' => 'single_text',
                'label' => 'Du',
                'required' => false,
                'attr' => [
                    'data-model' => 'on(change)|from',
                    'data-controller' => 'flatpickr',
                ],
                'constraints' => [
                    new Assert\Date(),
                ],
            ]
            )
            ->add('to', DateType::class, [
                'html5' => true,
                'widget' => 'single_text',
                'label' => 'Au',
                'required' => false,
                'attr' => [
                    'data-model' => 'on(change)|to',
                    'data-controller' => 'flatpickr',
                ],
                'constraints' => [
                    new Assert\Date(),
                ],
            ]
            )
            ->add('archived', ChoiceType::class, [
                'label' => 'Archivé',
                'required' => true,
                'attr' => [
                    'data-model' => 'archived',
                ],
                'choices' => [
                    'Non' => 'false',
                    'Oui' => 'true',
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de produit',
                'required' => true,
                'attr' => [
                    'data-model' => 'type',
                ],
                'choices' => [
                    'Tous' => null,
                    'Sponsoring' => 'sponsoring',
                    'Package VIP' => 'package',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
        ]);
    }
}
