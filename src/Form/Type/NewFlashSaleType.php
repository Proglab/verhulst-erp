<?php

namespace App\Form\Type;

use App\Entity\CompanyContact;
use App\Entity\FastSales;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;

class NewFlashSaleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder = new DynamicFormBuilder($builder);


        $builder->add('contact', EntityType::class, [
            'class' => CompanyContact::class,
            'label' => false,
            'required' => true,
            'constraints' => [
                new NotBlank()
            ],
            'attr' => [
                'class' => 'd-none'
            ],
        ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'label' => false,
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
                'attr' => [
                    'class' => 'd-none'
                ],
            ])

        ->add('name', TextType::class, [
            'label' => 'Nom du produit vendu',
            'required' => true,
            'constraints' => [
                new NotBlank()
            ],
        ])
        ->add('po', TextType::class, [
            'label' => 'Po',
            'required' => true,
            'constraints' => [
                new NotBlank()
            ],
        ])
        ->add('quantity', NumberType::class, [
            'label' => 'Quantité',
            'required' => true,
            'constraints' => [
                new NotBlank()
            ],
            'data' => 1
        ])
        ->add('percent_com', PercentType::class, [
            'label' => 'Pourcentage Vendeur',
            'required' => true,
            'mapped' => true,
            'constraints' => [
                new NotBlank()
            ],
            'scale' => 2,
            'type' => 'fractional',
        ])
        ->add('type_com', ChoiceType::class, [
            'label' => 'Pourcentage Verhulst',
            'attr' => [
                'class' => 'col-md-4 mb-3'
            ],
            'choices' => [
                'Pourcentage Verhulst' => 'percent',
                'Prix d\'achat' => 'price',
            ],
            'required' => true,
            'placeholder' => "Sélectionnez un type de commission pour Verhulst",
            'mapped' => false,
            'constraints' => [
                new NotBlank()
            ],
        ])
        ->addDependent('com1', 'type_com', function (DependentField $field, ?string $date_type) {
            if ($date_type === null) {
                return;
            }
            if ($date_type === 'percent') {
                $field->add(PercentType::class, [
                    'label' => 'Pourcentage Verhulst',
                    'required' => true,
                    'mapped' => false,
                    'constraints' => [
                        new NotBlank()
                    ],
                    'scale' => 2,
                    'type' => 'fractional',
                ]);
            } else {
                $field->add(MoneyType::class, [
                    'label' => 'Prix d\'achat',
                    'constraints' => [
                        new NotBlank()
                    ],
                    'mapped' => false,
                ]);
            }
        })
        ->add('forecast_price', MoneyType::class,  [
            'label' => 'Prix de vente unitaire prévisionnel',
            'required' => false,
            'mapped' => true,
        ])
        ->add('price', MoneyType::class,  [
            'label' => 'Prix de vente unitaire définitif',
            'required' => false,
            'mapped' => true,
        ])
        ->addDependent('validate', 'price', function (DependentField $field, ?string $money) {
            if ($money === null) {
                return;
            }

            $field->add(CheckboxType::class, [
                'label' => 'Valider la vente (vous ne pourrez plus modifier les informations)',
                'mapped' => false,
                'required' => false,
            ]);
        })
        ->add('date', DateType::class,  [
            'label' => 'Date de la vente',
            'required' => false,
            'mapped' => true,
            'data' => new \DateTime(),
            'html5' => true,
            'widget' => 'single_text',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FastSales::class,
        ]);
    }

}