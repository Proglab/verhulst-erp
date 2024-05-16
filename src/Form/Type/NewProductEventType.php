<?php
declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\ProductEvent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\UX\LiveComponent\Form\Type\LiveCollectionType;
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;

class NewProductEventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder = new DynamicFormBuilder($builder);

        $builder
            ->add('name', null, [
                'label' => 'Nom de l\'event',
                'attr' => [
                    'class' => 'col-md-8 mb-3'
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
            ])

            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'class' => 'col-md-12'
                ],
                'required' => false,
            ])
            ->add('percentFreelance', ChoiceType::class, [
                'label' => '% Freelance',
                'required' => true,
                'choices' => [
                    '10%' => 0.1,
                    '7.5%' => 0.075,
                    '4%' => 0.04,
                    '3.5%' => 0.035,
                    '3%' => 0.03,
                    'Autre' => 'other',
                ],
                'mapped' => false,
                'constraints' => [
                    new NotBlank()
                ],
                'placeholder' => 'Sélectionnez un %',
            ])
            ->addDependent('percentFreelanceCustom', 'percentFreelance', function (DependentField $field, ?string $date_type) {
                if ($date_type !== 'other') {
                    return;
                }
                $field->add(PercentType::class, [
                    'label' => '% Freelance',
                    'required' => true,
                    'mapped' => false,
                    'constraints' => [
                        new NotBlank()
                    ],
                    'scale' => 2,
                    'type' => 'fractional',
                ]);
            })
            ->add('percentSalarie', ChoiceType::class, [
                'label' => '% Salarié',
                'required' => true,
                'choices' => [
                    '10%' => 0.1,
                    '7.5%' => 0.075,
                    '4%' => 0.04,
                    '3.5%' => 0.035,
                    '3%' => 0.03,
                    'Autre' => 'other',
                ],
                'mapped' => false,
                'placeholder' => 'Sélectionnez un %',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->addDependent('percentSalarieCustom', 'percentSalarie', function (DependentField $field, ?string $date_type) {
                if ($date_type !== 'other') {
                    return;
                }
                $field->add(PercentType::class, [
                    'label' => '% Salarié',
                    'required' => true,
                    'mapped' => false,
                    'constraints' => [
                        new NotBlank()
                    ],
                    'scale' => 2,
                    'type' => 'fractional',
                ]);
            })
            ->add('percentTv', ChoiceType::class, [
                'label' => '% Thierry',
                'required' => true,
                'choices' => [
                    '10%' => 0.1,
                    '7.5%' => 0.075,
                    '4%' => 0.04,
                    '3.5%' => 0.035,
                    '3%' => 0.03,
                    'Autre' => 'other',
                ],
                'placeholder' => 'Sélectionnez un %',
                'mapped' => false,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->addDependent('percentTvCustom', 'percentTv', function (DependentField $field, ?string $date_type) {
                if ($date_type !== 'other') {
                    return;
                }
                $field->add(PercentType::class, [
                    'label' => '% Thierry',
                    'required' => true,
                    'mapped' => false,
                    'constraints' => [
                        new NotBlank()
                    ],
                    'scale' => 2,
                    'type' => 'fractional',
                ]);
            })
            ->add('type_date', ChoiceType::class, [
                'label' => 'Type de date',
                'choices' => [
                    'Début/fin' => 'date',
                    'Multiple' => 'date_multiple',
                ],
                'required' => true,
                'placeholder' => "Sélectionnez un type de date",
                'mapped' => false,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->addDependent('dates', 'type_date', function (DependentField $field, ?string $date_type) {
                if ($date_type === null) {
                    return;
                }
                if ($date_type === 'date') {
                    $field->add(DatesType::class, [
                        'label' => 'Dates',
                        'required' => true,
                        'mapped' => false,
                    ]);
                }
            })
            ->addDependent('dates2', 'type_date', function (DependentField $field, ?string $date_type) {
                if ($date_type === null) {
                    return;
                }
                if ($date_type === 'date_multiple') {
                    $field->add(LiveCollectionType::class, [
                        'entry_type' => DateType::class,
                        'entry_options' => [
                            'label' => 'Date',
                            'widget' => 'single_text',
                            'html5' => true,
                            'attr' => [
                                'class' => 'col-md-3 mb-3'
                            ],
                            'required' => true,
                            'mapped' => false,
                        ],
                        'allow_add' => true,
                        'allow_delete' => true,
                        'mapped' => false,
                    ]);
                }
            })
            ->add('type_com', ChoiceType::class, [
                'label' => 'Type de commission Verhulst',
                'attr' => [
                    'class' => 'col-md-4 mb-3'
                ],
                'choices' => [
                    'Pourcentage' => 'percent',
                    'Prix d\'achat:Prix de vente' => 'price',
                ],
                'required' => true,
                'placeholder' => "Sélectionnez un type de date",
                'mapped' => false,
            ])
            ->addDependent('com1', 'type_com', function (DependentField $field, ?string $date_type) {
                if ($date_type === null) {
                    return;
                }
                if ($date_type === 'percent') {
                    $field->add(PercentsType::class, [
                        'label' => false,
                        'attr' => [
                            'class' => 'col-md-3 mb-3',
                        ],
                        'required' => true,
                        'mapped' => false,
                    ]);
                }
            })
            ->addDependent('com2', 'type_com', function (DependentField $field, ?string $date_type) {
                if ($date_type === null) {
                    return;
                }
                if ($date_type === 'price') {
                    $field->add(PricesType::class, [
                        'label' => 'Prix',
                        'attr' => [
                            'class' => 'col-md-3 mb-3',
                        ],
                        'required' => true,
                        'mapped' => false,
                    ]);
                }
            });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductEvent::class,
        ]);
    }
}