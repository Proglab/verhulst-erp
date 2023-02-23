<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product;
use App\Entity\Sales;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sales>
 *
 * @method Sales|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sales|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sales[]    findAll()
 * @method Sales[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SalesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sales::class);
    }

    public function save(Sales $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sales $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getQuantitySaleByProduct(Product $product): int
    {
        $sql = 'SELECT SUM(`quantity`) as totalQuantity FROM `sales` WHERE `product_id` = ' . $product->getId() . ' GROUP BY `product_id`';
        $query = $this->getEntityManager()->getConnection()->executeQuery($sql);
        $result = $query->fetchOne();
        if (null === $result || false === $result) {
            return 0;
        }

        return (int) $result;
    }

    public function getStatsByMonth(int $year): array
    {
        $sql = 'SELECT SUM((`price` * `quantity`) - `discount`) as price , CONCAT(MONTH(date), "-", YEAR(date)) as date
            FROM `sales`
            WHERE YEAR(date) = "' . $year . '"
            GROUP BY CONCAT(MONTH(date), "-", YEAR(date))
            ORDER BY CONCAT(MONTH(date), "-", YEAR(date))';
        $query = $this->getEntityManager()->getConnection()->executeQuery($sql);

        return $query->fetchAllAssociative();
    }
}
