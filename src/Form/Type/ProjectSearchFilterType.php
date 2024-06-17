<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
                    'attr' => [
                        'data-model' => 'on(change)|to',
                        'data-controller' => 'flatpickr',
                    ],
                    'constraints' => [
                        new Assert\Date(),
                    ],
                ]
            )
            ->add('archived', CheckboxType::class, [
                    'label' => 'Archivé',
                    'required' => false,
                    'attr' => [
                        'data-model' => 'archived',
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
