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
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfonycasts\DynamicForms\DynamicFormBuilder;
use Symfony\Component\Validator\Constraints\File;

class StatsSalesProjectsMonthFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                    'html5' => true,
                    'widget' => 'single_text',
                    'label' => 'Du',
                    'attr' => [
                        'data-model' => 'on(change)|date',
                        'data-controller' => 'flatpickrmonth',
                    ],
                ]
            )
            ->add('user', EntityType::class, [
                    'class' => User::class,
                    'multiple' => false,
                    'expanded' => false,
                    'placeholder' => '',
                    'required' => true,
                    'query_builder' => function (UserRepository $er) {
                        return $er->getCommercialsQb();
                    },
                    'label' => 'Commercial',
                    'attr' => [
                        'data-model' => 'user',
                    ],
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