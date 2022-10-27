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
                        'autocomplete' => 'new-password',
                        'placeholder' => 'reset_password.reset.field.new_password',
                    ],
                ],
                'second_options' => [
                    'label' => false,
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'placeholder' => 'reset_password.reset.field.repeat_password',
                    ],
                ],
                'invalid_message' => 'reset_password.reset.form.invalid_passwords_message',
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
