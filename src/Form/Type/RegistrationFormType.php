<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\User;
use App\Validator\Constraints\SecurePassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationFormType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly bool $disableSecurePassword
    ) {
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
            ->add('email', EmailType::class, [
                'label' => 'register.form.email',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'register.form.email',
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'label' => 'password_update.form.newPassword',
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'register.form.password.placeholder',
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'placeholder' => 'register.form.password.placeholder',
                    ],
                ],
                'second_options' => [
                    'label' => 'register.form.password_confirm.placeholder',
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'placeholder' => 'register.form.password_confirm.placeholder',
                    ],
                ],
                'constraints' => $passwordConstraints,
                'mapped' => false,
            ])
            ->add('lastName', TextType::class, [
                'label' => 'register.form.lastName',
                'attr' => [
                    'placeholder' => 'register.form.lastName',
                    'class' => 'form-control',
                ],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'register.form.firstName',
                'attr' => [
                    'placeholder' => 'register.form.firstName',
                    'class' => 'form-control',
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'register.form.agreeTerms',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => $this->translator->trans('register.form.hasValidatedCGU.is_true_message', [], 'messages'),
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
