<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Form\Model\DoubleFactorAuthenticationSetup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DoubleFactorAuthenticationSetupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('code', TextType::class, [
            'label' => false,
            'attr' => [
                'placeholder' => 'Code de vérification',
                'class' => 'form-control-lg',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DoubleFactorAuthenticationSetup::class,
        ]);
    }
}
