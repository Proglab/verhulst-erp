<?php

declare(strict_types=1);

namespace App\Twig\Components;

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

#[AsLiveComponent('admin_search_global_contact_mail', template: 'admin/contact/components/searchContactMail.html.twig')]
class SearchContactMail
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public ?string $query = null;

    #[LiveProp(writable: true)]
    public int $count = 0;

    #[LiveProp(writable: true)]
    public int $page = 1;

    public int $pageNbr = 20;

    #[LiveProp(writable: true, onUpdated: 'onUserUpdated')]
    public ?string $user = null;

    public ?array $users = null;



    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository, /** @phpstan-ignore-line */
        private readonly PaginatorInterface $paginator,
        private RequestStack $requestStack)
    {
        $this->users = $userRepository->getCommercials();
        $session = $this->requestStack->getSession();
        $this->user = $session->get('userFilter', null);
        $this->page = $session->get('userFilterPage', 1);
    }

    public function onUserUpdated(mixed $previousValue): void
    {
        $this->page = 1;
        $session = $this->requestStack->getSession();
        $session->set('userFilter', $this->user);
    }

    public function mount(): void
    {
        $this->getContacts();
    }

    public function getContacts(): PaginationInterface
    {
        $user = '';
        if ($this->user) {
            $user = ' AND user.email = :user';
        }
        if (empty($this->query)) {
            $sql = '
            SELECT \'Contact validé\' as type, company.name, company.vat_number, company_contact.id, company_contact.firstname, company_contact.lastname, company_contact.lang, company_contact.email, company_contact.phone, company_contact.gsm, user.first_name, user.last_name, company_contact.note, company_contact.added_by_id
            FROM company_contact
            LEFT JOIN company ON company_contact.company_id = company.id
            LEFT JOIN user ON company_contact.added_by_id = user.id
            WHERE company.name IS NULL ' . $user;
            if ($this->user) {
                $stmt = $this->entityManager->getConnection()->prepare($sql);
                $result = $stmt->executeQuery(['user' => $this->user]);
            } else {
                $stmt = $this->entityManager->getConnection()->prepare($sql);
                $result = $stmt->executeQuery();
            }
        } else {

            $sql = '
            SELECT \'Contact validé\' as type, company.name, company.vat_number, company_contact.id, company_contact.firstname, company_contact.lastname, company_contact.lang, company_contact.email, company_contact.phone, company_contact.gsm, user.first_name, user.last_name, company_contact.note, company_contact.added_by_id
            FROM company_contact
            LEFT JOIN company ON company_contact.company_id = company.id
            LEFT JOIN user ON company_contact.added_by_id = user.id
            WHERE (company.name LIKE :query
            OR company_contact.firstname LIKE :query
            OR company_contact.lastname LIKE :query
            OR company_contact.email LIKE :query
            OR company_contact.phone LIKE :query
            OR company_contact.gsm LIKE :query
            OR CONCAT(company_contact.firstname, \' \',company_contact.lastname) LIKE :query)
            ' . $user . '
            UNION
            SELECT \'Import\' as type, temp_company.name, temp_company.vat_number, temp_company_contact.id, temp_company_contact.firstname, temp_company_contact.lastname, temp_company_contact.lang, temp_company_contact.email, temp_company_contact.phone, temp_company_contact.gsm, user.first_name, user.last_name, \'\', temp_company_contact.added_by_id
            FROM temp_company_contact
            JOIN temp_company ON temp_company_contact.company_id = temp_company.id
            LEFT JOIN user ON temp_company_contact.added_by_id = user.id
            WHERE (temp_company.name LIKE :query
            OR temp_company_contact.firstname LIKE :query
            OR temp_company_contact.lastname LIKE :query
            OR temp_company_contact.email LIKE :query
            OR temp_company_contact.phone LIKE :query
            OR temp_company_contact.gsm LIKE :query
            OR CONCAT(temp_company_contact.firstname, \' \',temp_company_contact.lastname) LIKE :query)
            ' . $user . '
            ORDER BY name, lastname ASC';

            $stmt = $this->entityManager->getConnection()->prepare($sql);
            $result = $stmt->executeQuery(['query' => '%' . $this->query . '%', 'user' => $this->user]);
        }
        $datas = $result->fetchAllAssociative();


        /** @var PaginationInterface $paginator */
        $paginator = $this->paginator->paginate($datas, $this->page, $this->pageNbr);
        $paginator->setTemplate('components/paginator.html.twig'); /* @phpstan-ignore-line */

        return $paginator;
    }

    #[LiveAction]
    public function getCount(): int
    {
        $user = '';
        if ($this->user) {
            $user = ' AND user.email = :user';
        }

        $sql = '
        SELECT COUNT(*) as nbr
        FROM company_contact
        JOIN company ON company_contact.company_id = company.id
        LEFT JOIN user ON company_contact.added_by_id = user.id
        WHERE (company.name LIKE :query
        OR company_contact.firstname LIKE :query
        OR company_contact.lastname LIKE :query
        OR company_contact.email LIKE :query
        OR company_contact.phone LIKE :query
        OR company_contact.gsm LIKE :query
        OR CONCAT(company_contact.firstname, \' \',company_contact.lastname) LIKE :query)
        ' . $user . '
        UNION
        SELECT COUNT(*) as nbr
        FROM temp_company_contact
        JOIN temp_company ON temp_company_contact.company_id = temp_company.id
        LEFT JOIN user ON temp_company_contact.added_by_id = user.id
        WHERE (temp_company.name LIKE :query
        OR temp_company_contact.firstname LIKE :query
        OR temp_company_contact.lastname LIKE :query
        OR temp_company_contact.email LIKE :query
        OR temp_company_contact.phone LIKE :query
        OR temp_company_contact.gsm LIKE :query
        OR CONCAT(temp_company_contact.firstname, \' \',temp_company_contact.lastname) LIKE :query)
        ' . $user;
        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $result = $stmt->executeQuery(['query' => '%' . $this->query . '%', 'user' => $this->user]);
        $datas = $result->fetchAllAssociative();
        $total = 0;
        foreach ($datas as $tot) {
            $total += $tot['nbr'];
        }

        return $total;
    }

    #[LiveAction]
    public function setPage(#[LiveArg] int $page): void
    {
        $this->page = $page;

        $session = $this->requestStack->getSession();
        $session->set('userFilterPage', $page);
    }
}
