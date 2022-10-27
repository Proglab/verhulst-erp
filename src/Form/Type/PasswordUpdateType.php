<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Form\Model\PasswordUpdate;
use App\Validator\Constraints\SecurePassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;

class PasswordUpdateType extends AbstractType
{
    public function __construct(private bool $disableSecurePassword)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $passwordConstraints = [
            new SecurePassword(),
        ];

        if (false === $this->disableSecurePassword) {
            $passwordConstraints[] = new NotCompromisedPassword();
        }

        $builder
            ->add('oldPassword', PasswordType::class, [
                'label' => 'password_update.form.oldPassword',
                'attr' => [
                    'placeholder' => 'password_update.form.oldPassword',
                ],
            ])
            ->add('newPassword', RepeatedType::class, [
                'label' => 'password_update.form.newPassword',
                'invalid_message' => 'Les mots de passe doivent être identiques.',
                'constraints' => $passwordConstraints,
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'password_update.form.newPassword',
                    'attr' => ['placeholder' => 'password_update.form.newPassword'],
                ],
                'second_options' => [
                    'label' => 'password_update.form.newPassword_confirm',
                    'attr' => ['placeholder' => 'password_update.form.newPassword_confirm'],
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PasswordUpdate::class,
        ]);
    }
}
