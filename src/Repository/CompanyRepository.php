<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Company>
 *
 * @method Company|null find($id, $lockMode = null, $lockVersion = null)
 * @method Company|null findOneBy(array $criteria, array $orderBy = null)
 * @method Company[]    findAll()
 * @method Company[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }

    public function save(Company $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Company $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function search(?string $search): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.contact', 'contact')
            ->where('c.name LIKE :search')
            ->orWhere('contact.firstname LIKE :search')
            ->orWhere('contact.lastname LIKE :search')
            ->orWhere('CONCAT(contact.lastname, \' \' ,contact.firstname) LIKE :search')
            ->orWhere('CONCAT(contact.firstname, \' \' ,contact.lastname) LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('c.name, contact.firstname, contact.lastname')
            ->getQuery()
            ->getResult();
    }
}
