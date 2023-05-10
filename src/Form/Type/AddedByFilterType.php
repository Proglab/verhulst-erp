<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\TempCompanyContact;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddedByFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('added_by', EntityType::class, [
                'class' => User::class,
                'query_builder' => function (UserRepository $er) {
                    $er->getCommercialsQb();
                },
                'choice_label' => 'fullname',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TempCompanyContact::class,
        ]);
    }
}
