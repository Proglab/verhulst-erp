<?php
declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Company;
use App\Entity\CompanyContact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfonycasts\DynamicForms\DynamicFormBuilder;

class NewCompanyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', null, [
            'label' => 'Nom de la société',
            'attr' => [
                'class' => 'col-md-8 mb-3'
            ],
            'required' => true,
            'constraints' => [
                new NotBlank()
            ],
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
        ]);
    }
}