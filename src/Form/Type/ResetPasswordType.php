<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Form\Model\ResetPasswordModel;
use App\Validator\Constraints\SecurePassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;

class ResetPasswordType extends AbstractType
{
    public function __construct(private bool $disableSecurePassword)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $passwordConstraints = [
            new SecurePassword(),
        ];

        if (false === (bool) $this->disableSecurePassword) {
            $passwordConstraints[] = new NotCompromisedPassword();
        }

        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'Nouveau mot de passe',
                    ],
                ],
                'second_options' => [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'Confirmer le mot de passe',
                    ],
                ],
                'invalid_message' => 'Les 2 mots de passe ne sont pas identiques.',
                'constraints' => $passwordConstraints,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ResetPasswordModel::class,
        ]);
    }
}
