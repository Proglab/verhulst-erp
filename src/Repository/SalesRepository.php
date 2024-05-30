<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Company;
use App\Entity\CompanyContact;
use App\Entity\Product;
use App\Entity\Project;
use App\Entity\Sales;
use App\Entity\User;
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
        /** @var Sales[] $sales */
        $sales = $this->createQueryBuilder('s')
            ->addSelect('p')
            ->join('s.product', 'p')
            ->where('s.date BETWEEN :start AND :end')
            ->setParameter('start', $year . '-01-01')
            ->setParameter('end', $year . '-12-31')
            ->getQuery()
            ->getResult();

        $datas = [];
        foreach ($sales as $sale) {
            if (!isset($datas[$sale->getDate()->format('m-Y')])) {
                $datas[$sale->getDate()->format('m-Y')] = $sale->getMarge();
            } else {
                $datas[$sale->getDate()->format('m-Y')] += $sale->getMarge();
            }
        }
        $return = [];
        foreach ($datas as $date => $price) {
            $return[] = ['date' => $date, 'price' => number_format($price, 2, '.', '')];
        }

        return $return;
    }

    public function getStatsByMonthByUser(int $year, User $user): array
    {
        /** @var Sales[] $sales */
        $sales = $this->createQueryBuilder('s')
            ->addSelect('p')
            ->join('s.product', 'p')
            ->where('s.date BETWEEN :start AND :end')
            ->setParameter('start', $year . '-01-01')
            ->setParameter('end', $year . '-12-31')
            ->andWhere('s.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        $datas = [];
        foreach ($sales as $sale) {
            if (!isset($datas[$sale->getDate()->format('m-Y')])) {
                $datas[$sale->getDate()->format('m-Y')] = $sale->getMarge();
            } else {
                $datas[$sale->getDate()->format('m-Y')] += $sale->getMarge();
            }
        }
        $return = [];
        foreach ($datas as $date => $price) {
            $return[] = ['date' => $date, 'price' => number_format($price, 2, '.', '')];
        }

        return $return;
    }

    public function getCommissionsStatsByMonthByUser(int $year, User $user): array
    {
        /** @var Sales[] $sales */
        $sales = $this->createQueryBuilder('s')
            ->addSelect('p')
            ->join('s.product', 'p')
            ->where('s.date BETWEEN :start AND :end')
            ->setParameter('start', $year . '-01-01')
            ->setParameter('end', $year . '-12-31')
            ->andWhere('s.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        $datas = [];
        foreach ($sales as $sale) {
            if (!isset($datas[$sale->getDate()->format('m-Y')])) {
                $datas[$sale->getDate()->format('m-Y')] = $sale->getEuroCom();
            } else {
                $datas[$sale->getDate()->format('m-Y')] += $sale->getEuroCom();
            }
        }
        $return = [];
        foreach ($datas as $date => $price) {
            $return[] = ['date' => $date, 'price' => number_format($price, 2, '.', '')];
        }

        return $return;
    }

    /**
     * @return Sales[]
     */
    public function get10LastSalesByUser(User $user): array
    {
        return $this->createQueryBuilder('s')
            ->addSelect('p')
            ->join('s.product', 'p')
            ->andWhere('s.user = :user')
            ->setParameter('user', $user)
            ->setMaxResults(10)
            ->orderBy('s.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Sales[]
     */
    public function get10LastSales(): array
    {
        return $this->createQueryBuilder('s')
            ->addSelect('p')
            ->join('s.product', 'p')
            ->setMaxResults(10)
            ->orderBy('s.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findLastSale(User $user, CompanyContact $companyContact): ?Sales
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.user = :user')
            ->setParameter('user', $user)
            ->andWhere('s.contact = :contact')
            ->setParameter('contact', $companyContact)
            ->andWhere('DATE(s.date) = :date')
            ->setParameter('date', (new \DateTime('now'))->format('Y-m-d'))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getSalesByYear(User $user, int $year): array
    {
        return $this->createQueryBuilder('s')
            ->addSelect('p')
            ->join('s.product', 'p')
            ->join('p.project', 'pr')
            ->join('s.contact', 'c')
            ->join('c.company', 'co')
            ->andWhere('s.user = :user')
            ->setParameter('user', $user)
            ->andWhere('YEAR(s.date) = :year')
            ->setParameter('year', $year)
            ->orderBy('pr.name', 'ASC')
            ->addOrderBy('co.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
