<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Company;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class NewCompanyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Basics
        $builder->add('name', null, [
            'label' => 'Nom de la société',
            'required' => true,
            'constraints' => [
                new NotBlank(),
            ],
        ]);
        $builder->add('street', null, [
            'label' => 'Rue',
            'required' => false,
        ]);
        $builder->add('pc', null, [
            'label' => 'CP',
            'required' => false,
        ]);
        $builder->add('city', null, [
            'label' => 'Ville',
            'required' => false,
        ]);
        $builder->add('country', null, [
            'label' => 'Pays',
            'required' => false,
        ]);
        $builder->add('note', null, [
            'label' => 'Note globale',
            'required' => false,
        ]);
        // VAT & Billing
        $builder->add('vat_number', null, [
            'label' => 'TVA',
            'required' => false,
        ]);
        $builder->add('vat_na', null, [
            'label' => 'Non assujetti',
            'required' => false,
        ]);
        $builder->add('billing_street', null, [
            'label' => 'Rue',
            'required' => false,
        ]);
        $builder->add('billing_pc', null, [
            'label' => 'CP',
            'required' => false,
        ]);
        $builder->add('billing_city', null, [
            'label' => 'Ville',
            'required' => false,
        ]);
        $builder->add('billing_country', null, [
            'label' => 'Pays',
            'required' => false,
        ]);
        $builder->add('billing_mail', null, [
            'label' => 'Email',
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
        ]);
    }
}
