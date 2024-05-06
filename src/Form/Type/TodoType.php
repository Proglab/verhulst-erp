<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\CompanyContact;
use App\Entity\TempCompanyContact;
use App\Entity\Todo;
use App\Entity\TodoType as TodoTypeEntity;
use App\Entity\User;
use App\Repository\CompanyContactRepository;
use App\Repository\TodoTypeRepository;
use App\Repository\UserRepository;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TodoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', EntityType::class, [
                'class' => TodoTypeEntity::class,
                'choice_label' => 'name',
                'query_builder' => function (TodoTypeRepository $todoTypeRepository) {
                    return $todoTypeRepository->createQueryBuilder('t')
                        ->orderBy('t.name', 'ASC');
                },
                'placeholder' => 'Type de tâche',
                'required' => true,
            ])

            ->add('todo', CKEditorType::class, [
            ])
            ->add('dateReminder', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Todo::class,
        ]);
    }
}
