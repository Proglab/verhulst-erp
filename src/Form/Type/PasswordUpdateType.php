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
                'label' => 'Mot de passe actuel',
                'attr' => [
                    'placeholder' => 'Mot de passe actuel',
                ],
            ])
            ->add('newPassword', RepeatedType::class, [
                'label' => 'Nouveau de passe',
                'attr' => [
                    'placeholder' => 'Votre mot de passe',
                ],
                'invalid_message' => 'Les mots de passe doivent être identiques.',
                'constraints' => $passwordConstraints,
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Nouveau de passe',
                    'attr' => ['placeholder' => 'Nouveau mot de passe'],
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr' => ['placeholder' => 'Confirmer le mot de passe'],
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
