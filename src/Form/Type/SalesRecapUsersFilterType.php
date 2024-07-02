<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\BaseSales;
use App\Entity\Project;
use App\Entity\User;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SalesRecapUsersFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_begin', DateType::class, [
                'label' => 'Date de début',
                'html5' => true,
                'widget' => 'single_text',
                'attr' => [
                    'data-model' => 'on(change)|date_begin',
                    'data-controller' => 'flatpickr',
                ],
                'mapped' => false,
            ]
            )
            ->add('date_end', DateType::class, [
                'label' => 'Date de fin',
                'html5' => true,
                'widget' => 'single_text',
                'attr' => [
                    'data-model' => 'on(change)|date_end',
                    'data-controller' => 'flatpickr',
                ],
                'mapped' => false,
            ]
            )
            ->add('user', EntityType::class, [
                'class' => User::class,
                'multiple' => false,
                'expanded' => true,
                'placeholder' => '',
                'required' => true,
                'query_builder' => function (UserRepository $er) {
                    return $er->getCommercialsQb();
                },
                'label' => 'Commercial',
            ]
            )
            ->add('project', EntityType::class, [
                'class' => Project::class,
                'multiple' => false,
                'expanded' => false,
                'placeholder' => '',
                'label' => 'Projet',
                'attr' => [
                    'data-model' => 'project',
                ],
                'required' => true,
                'query_builder' => function (ProjectRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->where('p.archive = false')
                        ->orderBy('p.name', 'ASC');
                },
                'mapped' => false,
            ]
            )
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
                    'Vente flash' => 'flash',
                ],
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BaseSales::class
        ]);
    }
}
