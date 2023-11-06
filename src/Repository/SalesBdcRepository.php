<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Sales;
use App\Entity\SalesBdc;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SalesBdc>
 *
 * @method SalesBdc|null find($id, $lockMode = null, $lockVersion = null)
 * @method SalesBdc|null findOneBy(array $criteria, array $orderBy = null)
 * @method SalesBdc[]    findAll()
 * @method SalesBdc[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SalesBdcRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SalesBdc::class);
    }

    public function save(SalesBdc $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SalesBdc $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneBySale(User $user, ?Sales $sale): ?SalesBdc
    {
        if (empty($sale)) {
            return null;
        }

        return $this->createQueryBuilder('bdc')
                ->join('bdc.sales', 'sales')
                ->andWhere('bdc.user = :user')
                ->setParameter('user', $user)
                ->andWhere('sales = :sale')
                ->setParameter('sale', $sale)
                ->getQuery()
                ->getOneOrNullResult();
    }
}
