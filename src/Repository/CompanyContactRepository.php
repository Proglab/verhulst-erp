<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CompanyContact;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CompanyContact>
 *
 * @method CompanyContact|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanyContact|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanyContact[]    findAll()
 * @method CompanyContact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompanyContact::class);
    }

    public function save(CompanyContact $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CompanyContact $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getUpdatedContact(User $user): ?array
    {
        $contacts = $this->createQueryBuilder('c')
            ->andWhere('c.added_by = :user')
            ->setParameter('user', $user)
            ->andWhere('c.updated_dt IS NOT NULL')
            ->andWhere('c.email IS NOT NULL')
            ->andWhere('c.email != \'\'')
            ->getQuery()
            ->getResult();

        return $contacts;
    }

    public function search(string $query, ?int $addedBy = null): array
    {
        // dd($addedBy);
        if (null !== $addedBy) {
            return $this->createQueryBuilder('c')
                ->leftJoin('c.company', 'company')
                ->where('c.added_by = :addedBy')
                ->setParameter('addedBy', $addedBy)
                ->andWhere('(c.email LIKE :query OR c.firstname LIKE :query OR c.lastname LIKE :query OR company.name LIKE :query)')
                ->setParameter('query', "%$query%")
                ->getQuery()
                ->getResult();
        }

        return $this->createQueryBuilder('c')
            ->leftJoin('c.company', 'company')
            ->andWhere('c.email LIKE :query')
            ->orWhere('c.firstname LIKE :query')
            ->orWhere('c.lastname LIKE :query')
            ->orWhere('company.name LIKE :query')
            ->setParameter('query', "%$query%")
            ->getQuery()
            ->getResult();
    }
}
