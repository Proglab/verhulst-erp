<?php
declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Project;
use App\Enum\ProjectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => 'Nom du projet',
                'attr' => [
                    'class' => 'col-md-6 mb-3'
                ],
                'required' => true,
            ])
            ->add('date_begin', DateType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'label' => 'Date de début',
                'attr' => [
                    'class' => 'col-md-3 mb-3'
                ],
                'required' => true,
            ])
            ->add('date_end', DateType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'label' => 'Date de fin',
                'attr' => [
                    'class' => 'col-md-3 mb-3'
                ],
                'required' => false,
            ])
            ->add('type', EnumType::class, [
                'label' => 'Type de produits',
                'class' => ProjectType::class,
                'attr' => [
                    'class' => 'col-md-12'
                ],
                'mapped' => false,
                'placeholder' => 'Choisir un type de produit',
                'required' => true,
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}