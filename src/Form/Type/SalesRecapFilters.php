<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Product;
use App\Entity\Project;
use App\Form\Model\ResetPasswordModel;
use App\Repository\ProductRepository;
use App\Repository\ProjectRepository;
use App\Validator\Constraints\SecurePassword;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;

class SalesRecapFilters extends AbstractType
{
    public function __construct(private ProjectRepository $projectRepository)
    {
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder = new DynamicFormBuilder($builder);
        $builder
            ->add('from', DateType::class, [
                    'html5' => true,
                    'widget' => 'single_text',
                    'label' => 'Du',
                ]
            )
            ->add('to', DateType::class, [
                    'html5' => true,
                    'widget' => 'single_text',
                    'label' => 'Au',
                ]
            )
            ->add('project', EntityType::class, [
                    'class' => Project::class,
                    'multiple' => false,
                    'expanded' => false,
                    'placeholder' => '',
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
            });
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
