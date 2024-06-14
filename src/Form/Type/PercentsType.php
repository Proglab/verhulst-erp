<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class PercentsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('percent_vr', PercentType::class, [
                'label' => '% The Friends',
                'required' => true,
                'mapped' => true,
                'constraints' => [
                    new NotBlank(),
                ],
                'scale' => 2,
                'type' => 'fractional',
            ])
            ->add('pv', MoneyType::class, [
                'label' => 'Prix de vente',
                'required' => true,
                'mapped' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
