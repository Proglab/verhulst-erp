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
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
            'label' => 'Numéro de PO',
            'required' => false,
        ])
        ->add('quantity', NumberType::class, [
            'label' => 'Quantité',
            'required' => true,
            'constraints' => [
                new NotBlank()
            ],
            'data' => 1
        ])
        ->add('type_com', ChoiceType::class, [
            'label' => 'Type de commission',
            'required' => true,
            'mapped' => false,
            'choices' => [
                '% sur la com TF' => 'percent_com',
                '% sur le PV' => 'percent_pv',
                'Prix fixe' => 'percent_pv',
            ],
        ])







        ->add('percent_com', PercentType::class, [
            'label' => '% Sales',
            'required' => true,
            'mapped' => true,
            'constraints' => [
                new NotBlank()
            ],
            'scale' => 2,
            'type' => 'fractional',
        ])







        ->add('type_com', ChoiceType::class, [
            'label' => '% The Friends',
            'attr' => [
                'class' => 'col-md-4 mb-3'
            ],
            'choices' => [
                '% The Friends' => 'percent',
                'Prix d\'achat' => 'price',
            ],
            'required' => true,
            'placeholder' => "Sélectionnez un type de commission pour The Friends",
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
                    'label' => '% The Friends',
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
        ->add('type_vente', ChoiceType::class, [
            'label' => 'Connaissez-vous le prix définitif ?',
            'attr' => [
                'class' => 'col-md-4 mb-3'
            ],
            'choices' => [
                'Oui' => '1',
                'Non' => '0',
            ],
            'required' => true,
            'placeholder' => "Sélectionnez une option",
            'mapped' => false,
            'constraints' => [
                new NotBlank()
            ],
        ])
        ->addDependent('forecast_price', 'type_vente', function (DependentField $field, ?string $sale_type) {
            if ($sale_type === '1' || $sale_type === null) {
                return;
            }
            $field->add(MoneyType::class, [
                'label' => 'Prix de vente unitaire prévisionnel',
                'constraints' => [
                    new NotBlank()
                ],
                'mapped' => true,
                'required' => true,
            ]);
        })
        ->addDependent('price', 'type_vente', function (DependentField $field, ?string $sale_type) {
            if ($sale_type !== '1' || $sale_type === null) {
                return;
            }
            $field->add(MoneyType::class, [
                'label' => 'Prix de vente unitaire définitif',
                'constraints' => [
                    new NotBlank()
                ],
                'mapped' => true,
                'required' => true,
            ]);
        })
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
            'label' => 'Date d\'encodage',
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
        ]);
    }

}