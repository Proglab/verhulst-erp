<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\FastSales;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FastSales>
 */
class FastSalesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FastSales::class);
    }

    public function save(FastSales $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FastSales $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByFilters(?\DateTime $min, ?\DateTime $max, ?array $users)
    {
        $qb = $this->createQueryBuilder('fs');

        if ($min) {
            $qb->andWhere('fs.date >= :min')
                ->setParameter('min', $min);
        }

        if ($max) {
            $qb->andWhere('fs.date <= :max')
                ->setParameter('max', $max);
        }

        if ($users) {
            $qb->andWhere('fs.user IN (:users)')
                ->setParameter('users', $users);
        }

        return $qb;
    }
}
