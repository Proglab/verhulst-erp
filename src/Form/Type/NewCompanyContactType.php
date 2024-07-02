<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Company;
use App\Entity\CompanyContact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;

class NewCompanyContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder = new DynamicFormBuilder($builder);
        if ($options['company_create']) {
            $builder
                ->add('company', NewCompanyType::class, [
                    'label' => 'Société',
                    'required' => true,
                    'constraints' => [
                        new NotBlank(),
                    ],
                ]);
        }

        $builder->add('firstname', TextType::class, [
            'label' => 'Prénom',
            'required' => false,
        ]);
        $builder->add('lastname', TextType::class, [
            'label' => 'Nom',
            'required' => false,
        ]);
        $builder->add('lang', null, [
            'label' => 'Langue',
            'required' => false,
            'mapped' => false,
            'data' => 'fr',
            'placeholder' => 'Sélectionnez une langue',
        ]);
        $builder->add('email', EmailType::class, [
            'label' => 'Email',
            'required' => false,
        ]);
        $builder->addDependent('mailing', 'email', function (DependentField $field, ?string $email) {
            if (empty($email)) {
                return;
            }
            $field->add(CheckboxType::class, [
                'label' => 'Ajouter au mailing ?',
                'required' => false,
                'mapped' => true,
                'attr' => [
                    'class' => 'form-check-input',
                    'checked' => 'checked',
                ],
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CompanyContact::class,
            'company' => null,
            'company_create' => false,
        ]);
        $resolver->setAllowedTypes('company', [Company::class, 'null']);
        $resolver->setAllowedTypes('company_create', 'boolean');
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars = array_merge($view->vars, [
            'company' => $options['company'],
            'company_create' => $options['company_create'],
        ]);
    }
}
