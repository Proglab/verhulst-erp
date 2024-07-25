<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\FastSales;
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

class UpdateFlashSaleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder = new DynamicFormBuilder($builder);

        $builder
        ->add('name', TextType::class, [
            'label' => 'Nom du produit vendu',
            'required' => true,
            'constraints' => [
                new NotBlank(),
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
                new NotBlank(),
            ],
            'data' => 1,
        ])
        ->add('percent_com_type', ChoiceType::class, [
            'label' => 'Commission sales',
            'required' => true,
            'mapped' => true,
            'placeholder' => 'Sélectionnez un type de commission',
            'choices' => [
                '% sur le PV' => 'percent_pv',
                '% sur la com TF' => 'percent_com',
                'Prix fixe' => 'fixed',
            ],
            'constraints' => [
                new NotBlank(),
            ],
            'disabled' => true,
        ])
        ->addDependent('percent_com', 'percent_com_type', function (DependentField $field, ?string $com_type) {
            if (null === $com_type) {
                return;
            }
            if ('percent_com' === $com_type) {
                $field->add(PercentType::class, [
                    'label' => '% sur la com TF',
                    'required' => true,
                    'mapped' => true,
                    'constraints' => [
                        new NotBlank(),
                    ],
                    'scale' => 2,
                    'type' => 'fractional',
                ]);
            }
            if ('percent_pv' === $com_type) {
                $field->add(PercentType::class, [
                    'label' => '% sur le prix de vente',
                    'required' => true,
                    'mapped' => true,
                    'constraints' => [
                        new NotBlank(),
                    ],
                    'scale' => 2,
                    'type' => 'fractional',
                ]);
            }
        })
        ->addDependent('percent_com_eur', 'percent_com_type', function (DependentField $field, ?string $com_type) {
            if (null === $com_type) {
                return;
            }
            if ('fixed' === $com_type) {
                $field->add(MoneyType::class, [
                    'label' => 'Prix fixe',
                    'constraints' => [
                        new NotBlank(),
                    ],
                    'mapped' => true,
                ]);
            }
        })
        ->add('percent_vr_type', ChoiceType::class, [
            'label' => '% The Friends',
            'attr' => [
                'class' => 'col-md-4 mb-3',
            ],
            'choices' => [
                '% The Friends' => 'percent',
                'Prix fixe' => 'fixed',
            ],
            'required' => true,
            'placeholder' => 'Sélectionnez un type de commission',
            'mapped' => true,
            'constraints' => [
                new NotBlank(),
            ],
            'disabled' => true,
        ])
        ->addDependent('percent_vr', 'percent_vr_type', function (DependentField $field, ?string $date_type) {
            if (null === $date_type) {
                return;
            }
            if ('percent' === $date_type) {
                $field->add(PercentType::class, [
                    'label' => '% The Friends',
                    'required' => true,
                    'mapped' => true,
                    'constraints' => [
                        new NotBlank(),
                    ],
                    'scale' => 2,
                    'type' => 'fractional',
                ]);
            }
        })
        ->addDependent('pa', 'percent_vr_type', function (DependentField $field, ?string $date_type) {
            if (null === $date_type) {
                return;
            }
            if ('fixed' === $date_type) {
                $field->add(MoneyType::class, [
                    'label' => 'Prix d\'achat',
                    'constraints' => [
                        new NotBlank(),
                    ],
                    'mapped' => true,
                ]);
            }
        })
        ->add('forecast_price', MoneyType::class, [
            'label' => 'Prix de vente unitaire prévisionnel',
            'constraints' => [
                new NotBlank(),
            ],
            'mapped' => true,
            'required' => true,
        ])
        ->add('price', MoneyType::class, [
            'label' => 'Prix de vente unitaire définitif',
            'constraints' => [
                new NotBlank(),
            ],
            'mapped' => true,
            'required' => true,
        ])
        ->add('validate', CheckboxType::class, [
            'label' => 'Valider la vente (vous ne pourrez plus modifier les informations)',
            'mapped' => true,
            'required' => false,
        ])
        ->add('date', DateType::class, [
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
            'data_class' => FastSales::class,
        ]);
    }
}
