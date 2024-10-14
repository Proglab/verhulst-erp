<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Company;
use App\Entity\CompanyContact;
use App\Entity\User;
use App\Repository\CompanyRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;

class CompanyContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder = new DynamicFormBuilder($builder);
        /*if ($options['company_create']) {
            $builder
                ->add('company', NewCompanyType::class, [
                    'label' => 'Société',
                    'required' => true,
                    'constraints' => [
                        new NotBlank(),
                    ],
                ]);
        }*/

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
            'required' => true,
            'autocomplete' => true,
            'placeholder' => 'Sélectionnez une langue',
        ]);
        $builder->add('sex', ChoiceType::class, [
            'label' => 'Genre',
            'required' => false,
            'choices' => ['Homme' => 'M', 'Femme' => 'F', 'Non binaire' => 'U'],
        ]);
        $builder->add('email', EmailType::class, [
            'label' => 'Email',
            'required' => true,
        ]);
        $builder->add('phone', TextType::class, [
            'label' => 'Téléphone',
            'required' => false,
        ]);
        $builder->add('gsm', TextType::class, [
            'label' => 'Gsm',
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

        $builder->add('street', TextType::class, [
            'label' => 'Rue',
            'required' => false,
        ]);
        $builder->add('pc', TextType::class, [
            'label' => 'Code postal',
            'required' => false,
        ]);
        $builder->add('city', TextType::class, [
            'label' => 'Ville',
            'required' => false,
        ]);
        $builder->add('country', CountryType::class, [
            'label' => 'Pays',
            'required' => false,
            'autocomplete' => true,
        ]);

        /*->add('users', EntityType::class, [
            'class' => User::class,
            'multiple' => true,
            'expanded' => true,
            'placeholder' => '',
            'required' => true,
            'query_builder' => function (UserRepository $er) {
                return $er->getCommercialsQb();
            },
            'label' => 'Commercial',
        ]
        )*/
        $builder->add('company', EntityType::class, [
            'class' => Company::class,
            'label' => 'Société',
            'placeholder' => '',
            'autocomplete' => true,
            'multiple' => false,
            'expanded' => false,
            'required' => false,
            'query_builder' => function (CompanyRepository $companyRepository) {
                return $companyRepository->createQueryBuilder('c')
                    ->orderBy('c.name', 'ASC');
            },
        ]);
        $builder->add('function', TextType::class, [
            'label' => 'Fonction',
            'required' => false,
        ]);
        $builder->add('interests', ChoiceType::class, [
            'label' => 'Intérêts',
            'required' => false,
            'multiple' => true,
            'choices' => [
                'Vip Sport' => ['Vip Football' => 'vip_football', 'Vip Tennis' => 'vip_tennis', 'Vip Padel' => 'vip_padel', 'Vip F1' => 'vip_f1', 'Vip Hockey' => 'hockey'],
                'Vip Culturel' => ['Concert' => 'concert', 'Gastro' => 'gastro'],
                'Event a la carte' => ['Teambuilding' => 'teambuilding', 'Incentive' => 'incentive', 'Family day' => 'family_day'],
                'Sponsoring' => ['Football' => 'sponsoring_football', 'Hockey' => 'sponsoring_hockey', 'Athlètes' => 'sponsoring_athletes', 'Led Boarding' => 'sponsoring_led_boarding', 'E-Sports' => 'esports'],
            ],
        ]);

        $builder->add('greeting', TextType::class, [
            'label' => 'Formule de politesse',
            'required' => false,
        ]);
        /*$builder->add('added_by.fullName', TextType::class, [
            'label' => 'Commercial',
            'required' => false,
            'disabled' => true,
        ]);*/
        $builder->add('added_by', EntityType::class, [
            'class' => User::class,
            'label' => 'Commercial',
            'placeholder' => '',
            'autocomplete' => true,
            'multiple' => false,
            'expanded' => false,
            'required' => false,
            'disabled' => true,
            'query_builder' => function (UserRepository $userRepository) {
                return $userRepository->getCommercialsQb();
            },
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CompanyContact::class,
            // 'company' => null,
            // 'company_create' => false,
        ]);
        // $resolver->setAllowedTypes('company', [Company::class, 'null']);
        // $resolver->setAllowedTypes('company_create', 'boolean');
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars = array_merge($view->vars, [
            // 'company' => $options['company'],
            // 'company_create' => $options['company_create'],
        ]);
    }
}
