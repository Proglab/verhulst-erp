<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Company;
use App\Repository\CompanyContactRepository;
use App\Repository\CompanyRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('society_choice', template: 'app/sales/flash/componentsSearchContact.html.twig')]
class SocietyChoice
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public ?string $queryCompany = null;

    #[LiveProp(writable: true)]
    public ?string $queryContact = null;

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
}
