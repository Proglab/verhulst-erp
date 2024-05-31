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
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfonycasts\DynamicForms\DynamicFormBuilder;
use Symfony\Component\Validator\Constraints\File;

class StatsSalesProjectsYearFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = [];
        for ($i = date('Y'); $i >=  2022; $i--) {
            $choices[$i] = $i;
        }


        $builder
            ->add('date', ChoiceType::class, [
                    'label' => 'Année',
                    'attr' => [
                        'data-model' => 'date',
                    ],
                    'choices' => $choices,
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