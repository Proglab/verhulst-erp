<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\CompanyContact;
use App\Entity\TempCompanyContact;
use App\Entity\Todo;
use App\Entity\User;
use App\Repository\CompanyContactRepository;
use App\Repository\UserRepository;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TodoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('todo', CKEditorType::class, [
            ])
            ->add('dateReminder', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'yyyy-MM-dd HH:mm',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Todo::class,
        ]);
    }
}
