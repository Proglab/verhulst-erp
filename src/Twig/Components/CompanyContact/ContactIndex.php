<?php

declare(strict_types=1);

namespace App\Twig\Components\CompanyContact;

use App\Entity\CompanyContact;
use App\Repository\CompanyContactRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('contact-index', template: 'app/contact/components/index.html.twig')]
class ContactIndex extends AbstractController
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public ?string $query = null;

    #[LiveProp(writable: true)]
    public int $page = 1;

    #[LiveProp(writable: true)]
    public ?CompanyContact $toDelete = null;

    public function __construct(
        private CompanyContactRepository $companyContactRepository,
        private PaginatorInterface $paginator,
    ) {
    }

    public function contacts(): PaginationInterface
    {
        if (null === $this->query) {
            $qb = $this->companyContactRepository->findBy([], ['firstname' => 'ASC', 'lastname' => 'ASC']);
        } else {
            $qb = $this->companyContactRepository->search($this->query);
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
        $this->toDelete = $this->companyContactRepository->find($id);
    }

    #[LiveAction]
    public function abordDelete(): void
    {
        $this->toDelete = null;
    }

    #[LiveAction]
    public function confirmDelete(#[LiveArg] int $id): RedirectResponse
    {
        $contact = $this->companyContactRepository->find($id);
        $contact->setCompany(null);
        $this->companyContactRepository->remove($contact, true);
        $this->toDelete = null;

        // $this->dispatchBrowserEvent('modal:close');
        return $this->redirectToRoute('contact_index', []);
    }
}
