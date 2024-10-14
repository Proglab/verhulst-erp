<?php

declare(strict_types=1);

namespace App\Twig\Components\CompanyContact;

use App\Entity\CompanyContact;
use App\Repository\CompanyContactRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('contact-details', template: 'app/contact/components/detail.html.twig')]
class ContactDetail
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public ?CompanyContact $contact = null;

    #[LiveProp(writable: true)]
    public ?CompanyContact $toDelete = null;

    public function __construct(
        private CompanyContactRepository $companyContactRepository,
    ) {
    }

    #[LiveAction]
    public function delete(#[LiveArg] int $id): void
    {
        $this->toDelete = $this->companyContactRepository->find($id);
    }

    #[LiveAction]
    public function abordDelete(): void
    {
        $this->toDelete = null;
    }

    #[LiveAction]
    public function confirmDelete(#[LiveArg] int $id): void
    {
        $contact = $this->companyContactRepository->find($id);
        $contact->setCompany(null);
        $this->companyContactRepository->remove($contact, true);
        $this->toDelete = null;
        $this->dispatchBrowserEvent('modal:close');
    }
}
