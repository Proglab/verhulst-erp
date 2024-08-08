<?php

declare(strict_types=1);

namespace App\Twig\Components\Company;

use App\Entity\Company;
use App\Entity\CompanyContact;
use App\Repository\CompanyContactRepository;
use App\Repository\CompanyRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Event\PostRenderEvent;
use Symfony\UX\TwigComponent\Event\PreRenderEvent;

#[AsLiveComponent('company-details', template: 'app/company/components/detail.html.twig')]
class CompanyDetail
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public ?Company $company = null;

    #[LiveProp(writable: true)]
    public ?CompanyContact $contact = null;

    public function __construct(
        private CompanyRepository $companyRepository,
        private CompanyContactRepository $companyContactRepository,
    ) {
    }

    #[LiveAction]
    public function viewContact(#[LiveArg] int $contact): void
    {
        $this->contact = $this->companyContactRepository->find($contact);
    }
}
