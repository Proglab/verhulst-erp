<?php

declare(strict_types=1);

namespace App\Twig\Components\Company;

use App\Entity\Company;
use App\Repository\CompanyContactRepository;
use App\Repository\CompanyRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('company-index', template: 'app/company/components/index.html.twig')]
class CompanyIndex
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public ?string $query = null;

    #[LiveProp(writable: true)]
    public int $page = 1;

    #[LiveProp(writable: true)]
    public ?Company $toDelete = null;

    public function __construct(
        private CompanyRepository $companyRepository,
        private CompanyContactRepository $companyContactRepository,
        private PaginatorInterface $paginator,
    ) {
    }

    public function companies(): PaginationInterface
    {
        if (null === $this->query) {
            $qb = $this->companyRepository->findBy([], ['name' => 'ASC']);
        } else {
            $qb = $this->companyRepository->search($this->query);
        }

        return $this->paginator->paginate($qb, $this->page, 12);
    }

    #[LiveAction]
    public function previousPage(): void
    {
        --$this->page;
    }

    #[LiveAction]
    public function nextPage(): void
    {
        ++$this->page;
    }

    #[LiveAction]
    public function gotoPage(#[LiveArg] int $page): void
    {
        $this->page = $page;
    }

    #[LiveAction]
    public function delete(#[LiveArg] int $id): void
    {
        $this->toDelete = $this->companyRepository->find($id);
    }

    #[LiveAction]
    public function abordDelete(): void
    {
        $this->toDelete = null;
    }

    #[LiveAction]
    public function confirmDelete(#[LiveArg] int $id): void
    {
        $company = $this->companyRepository->find($id);
        $contacts = $company->getContact();
        foreach ($contacts as $contact) {
            $contact->setCompany(null);
            $this->companyContactRepository->save($contact);
        }
        $this->companyRepository->save($company);

        $this->companyRepository->remove($company, true);
        $this->toDelete = null;
        $this->dispatchBrowserEvent('modal:close');
    }
}
