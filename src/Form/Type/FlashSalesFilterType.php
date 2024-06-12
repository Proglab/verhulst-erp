<?php
declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class FlashSalesFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('min', DateType::class, [
                    'label' => 'Du',
                    'attr' => [
                        'data-model' => 'min',
                        'data-controller' => 'flatpickr',
                    ],
                    'html5' => true,
                    'widget' => 'single_text',
                    'constraints' => [
                        new Assert\Date(),
                    ],
                ]
            )
            ->add('max', DateType::class, [
                    'label' => 'Au',
                    'attr' => [
                        'data-model' => 'max',
                        'data-controller' => 'flatpickr',
                    ],
                    'html5' => true,
                    'widget' => 'single_text',
                    'constraints' => [
                        new Assert\Date(),
                    ],
                ]
            )
            ->add('users', EntityType::class, [
                    'class' => User::class,
                    'multiple' => true,
                    'expanded' => true,
                    'placeholder' => '',
                    'required' => true,
                    'query_builder' => function (UserRepository $er) {
                        return $er->getCommercialsQb();
                    },
                    'label' => 'Commercial'
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([

        ]);
    }
}