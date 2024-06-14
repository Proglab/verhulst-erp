<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Company;
use App\Entity\CompanyContact;
use App\Form\Type\NewCompanyContactType;
use App\Repository\CompanyContactRepository;
use App\Repository\CompanyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('society_choice', template: 'app/sales/flash/componentsSearchContact.html.twig')]
class SocietyChoice extends AbstractController
{
    use ComponentToolsTrait;
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public ?string $queryCompany = null;

    #[LiveProp(writable: true)]
    public ?string $queryContact = null;

    #[LiveProp(writable: true)]
    public bool $contact_create = false;

    #[LiveProp(writable: true)]
    public bool $company_create = false;

    #[LiveProp(writable: true)]
    public ?CompanyContact $contact = null;

    #[LiveProp(writable: true)]
    public ?Company $company = null;

    public function __construct(private CompanyRepository $companyRepository, private CompanyContactRepository $companyContactRepository)
    {
    }

    public function getSocieties(): array
    {
        if (empty($this->queryCompany)) {
            return [];
        }

        return $this->companyRepository->search($this->queryCompany);
    }

    public function getContacts(): array
    {
        if (empty($this->queryContact)) {
            return [];
        }

        return $this->companyContactRepository->search($this->queryContact);
    }

    #[LiveAction]
    public function selectContact(#[LiveArg('id')] int $contactId)
    {
        $this->contact = $this->companyContactRepository->find($contactId);
        $this->company = $this->contact->getCompany();

        return $this->redirectToRoute('sales_flash_create_sale', [
            'contact' => $this->contact->getId(),
        ]);
    }

    #[LiveAction]
    public function selectCompany(#[LiveArg('id')] int $companyId)
    {
        $this->company_create = false;
        $this->company = $this->companyRepository->find($companyId);
        $this->dispatchBrowserEvent('modal:open');
    }

    #[LiveAction]
    public function createContact()
    {
        $this->contact_create = true;
        $this->contact = null;
    }

    #[LiveAction]
    public function createCompany()
    {
        $this->company_create = true;
        $this->company = null;
        $this->dispatchBrowserEvent('modal:open');
    }

    #[LiveAction]
    public function noCompany()
    {
        $this->company_create = false;
        $this->company = null;
        $this->dispatchBrowserEvent('modal:open');
    }

    #[LiveAction]
    public function save()
    {
        $this->submitForm();

        /** @var CompanyContact $companyContact */
        $companyContact = $this->getForm()->getData();

        if ($this->company_create) {
            $company = new Company();
            $company->setName($companyContact->getCompany()->getName());
            $companyContact->setCompany($company);
        }

        if (!$this->contact_create && !empty($this->company)) {
            $companyContact->setCompany($this->company);
        }

        if (!empty($this->form->get('email')->getData())) {
            if (true === $this->form->get('mailing')->getData()) {
                $companyContact->setMailing(true);
            } else {
                $companyContact->setMailing(false);
            }
        } else {
            $companyContact->setMailing(false);
        }

        $companyContact->setAddedBy($this->getUser());

        $this->companyContactRepository->save($companyContact, true);

        return $this->redirectToRoute('sales_flash_create_sale', [
            'contact' => $companyContact->getId(),
        ]);
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(NewCompanyContactType::class, null, [
            'company' => $this->company,
            'company_create' => $this->company_create,
        ]);
    }
}
