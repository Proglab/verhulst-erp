<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Company;
use App\Entity\CompanyContact;
use App\Entity\Product;
use App\Entity\Project;
use App\Entity\User;
use App\Repository\CompanyContactRepository;
use App\Repository\CompanyRepository;
use App\Repository\ProductRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;

class SalesRecapFilters extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder = new DynamicFormBuilder($builder);
        $builder
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
                    'data-model' => 'to',
                    'data-controller' => 'flatpickr',
                ],
                'constraints' => [
                    new Assert\Date(),
                ],
            ]
            )
            ->add('project', EntityType::class, [
                'class' => Project::class,
                'multiple' => false,
                'expanded' => false,
                'placeholder' => '',
                'label' => 'Projet',
                'required' => false,
                'query_builder' => function (ProjectRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->where('p.archive = false')
                        ->orderBy('p.name', 'ASC');
                },
            ]
            )
            ->addDependent('product', ['project'], function (DependentField $field, ?Project $project) {
                if (empty($project)) {
                    return;
                }
                $field->add(EntityType::class, [
                    'class' => Product::class,
                    'choice_label' => 'name',
                    'attr' => [
                        'data-model' => 'product',
                    ],
                    'query_builder' => function (ProductRepository $er) use ($project) {
                        return $er->createQueryBuilder('p')
                            ->join('p.project', 'project')
                            ->where('project.id = :project')
                            ->andWhere('project.archive = false')
                            ->setParameter('project', $project)
                            ->orderBy('p.name', 'ASC');
                    },
                    'label' => 'Produit',
                    'required' => false,
                ]);
            })
            ->add('company', EntityType::class, [
                'class' => Company::class,
                'multiple' => false,
                'expanded' => false,
                'placeholder' => '',
                'required' => false,
                'query_builder' => function (CompanyRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                },
                'label' => 'Société',
            ]
            )
            ->addDependent('contact', ['company'], function (DependentField $field, ?Company $company) {
                if (empty($company)) {
                    return;
                }
                $field->add(EntityType::class, [
                    'class' => CompanyContact::class,
                    'choice_label' => 'lastname',
                    'attr' => [
                        'data-model' => 'contact',
                    ],
                    'query_builder' => function (CompanyContactRepository $er) use ($company) {
                        return $er->createQueryBuilder('c')
                            ->join('c.company', 'company')
                            ->where('company.id = :company')
                            ->setParameter('company', $company)
                            ->orderBy('c.lastname', 'ASC');
                    },
                    'label' => 'Contact',
                    'required' => false,
                ]);
            })
            ->add('user', EntityType::class, [
                'class' => User::class,
                'multiple' => false,
                'expanded' => false,
                'placeholder' => '',
                'required' => false,
                'query_builder' => function (UserRepository $er) {
                    return $er->getCommercialsQb();
                },
                'label' => 'Commercial',
                'attr' => [
                    'data-model' => 'user',
                ],
            ]
            )
            ->add('archive', ChoiceType::class, [
                'choices' => [
                    'Tous' => 'all',
                    'Non' => '0',
                    'Oui' => '1',
                ],
                'multiple' => false,
                'expanded' => false,
                'required' => true,
                'label' => 'Archivé ?',
                'attr' => [
                    'data-model' => 'archive',
                ],
            ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
