<?php

declare(strict_types=1);

namespace App\Twig\Components\Company;

use App\Entity\Company;
use App\Form\Type\NewCompanyType;
use App\Repository\CompanyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('company_form', template: 'app/company/components/form.html.twig')]
class CompanyForm extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public string $locale;

    #[LiveProp(writable: true)]
    public ?Company $company = null;

    public function __construct(private CompanyRepository $companyRepository, private KernelInterface $kernel, private ValidatorInterface $validator)
    {
    }

    #[LiveAction]
    public function save(): RedirectResponse
    {
        $this->submitForm();
        /** @var Company $post */
        $post = $this->getForm()->getData();

        $this->companyRepository->save($post, true);

        $this->addFlash('success', 'Company saved!');

        return $this->redirectToRoute('company_details', ['company' => $post->getId()]);
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(NewCompanyType::class, $this->company);
    }
}
