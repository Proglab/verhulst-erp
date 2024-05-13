<?php
declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\ProductEvent;
use App\Entity\ProductSponsoring;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\UX\LiveComponent\Form\Type\LiveCollectionType;
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;

class NewProductSponsoringType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder = new DynamicFormBuilder($builder);

        $builder
            ->add('name', null, [
                'label' => 'Nom du sponsoring',
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
                'required' => true,
            ])
            ->add('percentFreelance', PercentType::class, [
                'label' => '% Freelance',
                'attr' => [
                    'class' => 'col-md-4 mb-3'
                ],
                'required' => true,
                'data' => 0.1,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('percentSalarie', PercentType::class, [
                'label' => '% Salarié',
                'attr' => [
                    'class' => 'col-md-4 mb-3'
                ],
                'required' => true,
                'data' => 0.05,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('percentTv', PercentType::class, [
                'label' => '% Thierry',
                'attr' => [
                    'class' => 'col-md-4 mb-3'
                ],
                'required' => true,
                'data' => 0.03,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('quantityMax', NumberType::class, [
                'label' => 'Quantité maximale',
                'attr' => [
                    'class' => 'col-md-4'
                ],
                'required' => true,
            ])

            ->add('type_date', ChoiceType::class, [
                'label' => 'Type de date',
                'attr' => [
                    'class' => 'col-md-4 mb-3'
                ],
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
                        'attr' => [
                            'class' => 'col-md-6 mb-3',
                                'type' => 'date',
                        ],
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
                'label' => 'Type de commission',
                'attr' => [
                    'class' => 'col-md-4 mb-3'
                ],
                'choices' => [
                    'Pourcentage Verhulst' => 'percent',
                    'Prix d\'achat' => 'price',
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
                            'type' => 'date',
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
                            'type' => 'date',
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
            'data_class' => ProductSponsoring::class,
        ]);
    }
}