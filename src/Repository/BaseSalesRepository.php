<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\BaseSales;
use App\Entity\Company;
use App\Entity\CompanyContact;
use App\Entity\FastSales;
use App\Entity\Product;
use App\Entity\Project;
use App\Entity\Sales;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BaseSales>
 *
 * @method BaseSales|null find($id, $lockMode = null, $lockVersion = null)
 * @method BaseSales|null findOneBy(array $criteria, array $orderBy = null)
 * @method BaseSales[]    findAll()
 * @method BaseSales[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BaseSalesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BaseSales::class);
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

    public function getSalesStatsByMonthByUser(int $year, User $user, ?Project $project): array
    {
         $qb = $this->createQueryBuilder('s')
            ->addSelect('p')
            ->leftJoin('s.product', 'p')
            ->leftJoin('p.project', 'project')
            ->where('s.date BETWEEN :start AND :end')
            ->setParameter('start', $year . '-01-01')
            ->setParameter('end', $year . '-12-31')
            ->andWhere('s.user = :user')
            ->setParameter('user', $user);

         if (!empty($project)) {
             $qb
                 ->andWhere('project = :project')
                 ->setParameter('project', $project);
         }


        /** @var Sales[] $sales */
        $sales = $qb->getQuery()
            ->getResult();
        $datas = [];
        foreach ($sales as $sale) {
            if (!isset($datas[$sale->getDate()->format('m-Y')])) {
                $datas[$sale->getDate()->format('m-Y')] = $sale->getPrice() * $sale->getQuantity();
            } else {
                $datas[$sale->getDate()->format('m-Y')] += $sale->getPrice() * $sale->getQuantity();
            }
        }
        $return = [];
        foreach ($datas as $date => $price) {
            $return[] = ['date' => $date, 'price' => number_format($price, 2, '.', '')];
        }

        return $return;
    }

    public function getSalesStatsByYearByUser(int $year, User $user, ?array $projects): array
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.product', 'p')
            ->leftJoin('p.project', 'project')
            ->where('project.date_begin >= :dateBegin')
            ->setParameter('dateBegin', new \DateTime($year . '-01-01'))
            ->andWhere('project.date_end <= :dateEnd')
            ->setParameter('dateEnd', new \DateTime($year . '-12-31'))
            ->andWhere('s.user = :user')
            ->setParameter('user', $user)
            ->orderBy('project.name', 'ASC');

        /** @var Sales[] $sales */
        $sales = $qb->getQuery()
            ->getResult();

        $datas = [];
        foreach ($sales as $sale) {
            $project = strtolower($sale->getProduct()->getProject()->getName());
            if (!isset($datas[$project])) {
                $datas[$project] = $sale->getPrice() * $sale->getQuantity();
            } else {
                $datas[$project] += $sale->getPrice() * $sale->getQuantity();
            }
        }

        foreach ($projects as $project) {
            if (!isset($datas[strtolower($project->getName())])) {
                $datas[strtolower($project->getName())] = 0;
            }
        }

        ksort($datas, CASE_LOWER);

        $return = [];

        foreach ($datas as $project => $price) {
            $return[] = ['project' => $project, 'price' => number_format($price, 2, '.', '')];
        }
        return $return;
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


    public function search(?\DateTime $from, ?\DateTime $to, ?Project $project, ?Product $product, ?Company $company, ?CompanyContact $contact, ?User $user, ?bool $archive): array
    {
        $qb = $this->createQueryBuilder('s')
            ->addSelect('c')
            ->leftJoin('s.contact', 'c')
            ->leftJoin('s.product', 'product')
            ->leftJoin('product.project', 'project');


        if (null !== $archive) {
            $qb
                ->andWhere('project.archive = :archive')
                ->orWhere('project.archive IS NULL')
                ->setParameter('archive', $archive);
        }

        if ($from) {
            $qb->andWhere('s.date >= :from')
                ->setParameter('from', $from);
        }

        if ($to) {
            $qb->andWhere('s.date <= :to')
                ->setParameter('to', $to);
        }

        if ($product) {
            $qb
                ->andWhere('s.product = :product')
                ->setParameter('product', $product);
        }

        if ($project) {
            $qb
                ->andWhere('product.project = :project')
                ->setParameter('project', $project);
        }

        if ($company) {
            $qb->andWhere('c.company = :company')
                ->setParameter('company', $company);
        }

        if ($contact) {
            $qb->andWhere('s.contact = :contact')
                ->setParameter('contact', $contact);
        }

        if ($user) {
            $qb->andWhere('s.user = :user')
                ->setParameter('user', $user);
        }

        return $qb
            ->orderBy('s.date', 'DESC')
            ->getQuery()
            ->getResult();
    }


    public function searchTotal(?\DateTime $from, ?\DateTime $to, ?Project $project, ?Product $product, ?Company $company, ?CompanyContact $contact, ?User $user, ?bool $archive): float
    {
        $qb = $this->createQueryBuilder('s')
            ->select('SUM(s.quantity * s.price) as total')
            ->leftJoin('s.contact', 'c')
            ->leftJoin('s.product', 'product')
            ->leftJoin('product.project', 'project');


        if (null !== $archive) {
            $qb
                ->andWhere('project.archive = :archive')
                ->orWhere('project.archive IS NULL')
                ->setParameter('archive', $archive);
        }

        if ($from) {
            $qb->andWhere('s.date >= :from')
                ->setParameter('from', $from);
        }

        if ($to) {
            $qb->andWhere('s.date <= :to')
                ->setParameter('to', $to);
        }

        if ($product) {
            $qb
                ->andWhere('s.product = :product')
                ->setParameter('product', $product);
        }

        if ($project) {
            $qb
                ->andWhere('product.project = :project')
                ->setParameter('project', $project);
        }

        if ($company) {
            $qb->andWhere('c.company = :company')
                ->setParameter('company', $company);
        }

        if ($contact) {
            $qb->andWhere('s.contact = :contact')
                ->setParameter('contact', $contact);
        }

        if ($user) {
            $qb->andWhere('s.user = :user')
                ->setParameter('user', $user);
        }

        return (float) $qb
            ->orderBy('s.date', 'DESC')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function searchTotalCom(?\DateTime $from, ?\DateTime $to, ?Project $project, ?Product $product, ?Company $company, ?CompanyContact $contact, ?User $user, ?bool $archive): float
    {
        $qb = $this->createQueryBuilder('s')
            ->select('SUM(s.quantity * s.price * s.percent_com / 100) as total')
            ->leftJoin('s.contact', 'c')
            ->leftJoin('s.product', 'product')
            ->leftJoin('product.project', 'project');



        if (null !== $archive) {
            $qb
                ->andWhere('project.archive = :archive')
                ->orWhere('project.archive IS NULL')
                ->setParameter('archive', $archive);
        }

        if ($from) {
            $qb->andWhere('s.date >= :from')
                ->setParameter('from', $from);
        }

        if ($to) {
            $qb->andWhere('s.date <= :to')
                ->setParameter('to', $to);
        }

        if ($product) {
            $qb
                ->andWhere('s.product = :product')
                ->setParameter('product', $product);
        }

        if ($project) {
            $qb
                ->andWhere('product.project = :project')
                ->setParameter('project', $project);
        }

        if ($company) {
            $qb->andWhere('c.company = :company')
                ->setParameter('company', $company);
        }

        if ($contact) {
            $qb->andWhere('s.contact = :contact')
                ->setParameter('contact', $contact);
        }

        if ($user) {
            $qb->andWhere('s.user = :user')
                ->setParameter('user', $user);
        }

        return (float) $qb
            ->orderBy('s.date', 'DESC')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function searchTotalVr(?\DateTime $from, ?\DateTime $to, ?Project $project, ?Product $product, ?Company $company, ?CompanyContact $contact, ?User $user, ?bool $archive): float
    {
        $qb = $this->createQueryBuilder('s')
            ->select('SUM(s.quantity * s.price * s.percent_vr / 100) as total')
            ->leftJoin('s.contact', 'c')
            ->leftJoin('s.product', 'product')
            ->leftJoin('product.project', 'project');



        if (null !== $archive) {
            $qb

                ->andWhere('project.archive = :archive')
                ->orWhere('project.archive IS NULL')
                ->setParameter('archive', $archive);
        }

        if ($from) {
            $qb->andWhere('s.date >= :from')
                ->setParameter('from', $from);
        }

        if ($to) {
            $qb->andWhere('s.date <= :to')
                ->setParameter('to', $to);
        }

        if ($product) {
            $qb
                ->andWhere('s.product = :product')
                ->setParameter('product', $product);
        }

        if ($project) {
            $qb
                ->andWhere('product.project = :project')
                ->setParameter('project', $project);
        }

        if ($company) {
            $qb->andWhere('c.company = :company')
                ->setParameter('company', $company);
        }

        if ($contact) {
            $qb->andWhere('s.contact = :contact')
                ->setParameter('contact', $contact);
        }

        if ($user) {
            $qb->andWhere('s.user = :user')
                ->setParameter('user', $user);
        }

        return (float) $qb
            ->orderBy('s.date', 'DESC')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return BaseSales[]
     */
    public function get10LastSalesByUser(User $user): array
    {
        return $this->createQueryBuilder('s')
            ->addSelect('p')
            ->leftJoin('s.product', 'p')
            ->andWhere('s.user = :user')
            ->setParameter('user', $user)
            ->setMaxResults(10)
            ->orderBy('s.date', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
