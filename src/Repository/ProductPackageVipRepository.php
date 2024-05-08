<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ProductPackageVip;
use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductPackageVip>
 *
 * @method ProductPackageVip|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductPackageVip|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductPackageVip[]    findAll()
 * @method ProductPackageVip[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductPackageVipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductPackageVip::class);
    }

    public function save(ProductPackageVip $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductPackageVip $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function searchEventsByProject(Project $project, ?string $queryEvent)
    {
        $qb = $this->createQueryBuilder('p')
            ->join('p.project', 'pr')
            ->andWhere('pr = :project')
            ->setParameter('project', $project)
            ->andWhere('p.name LIKE :query')
            ->setParameter('query', '%' . $queryEvent . '%')
            ->orderBy('p.date_begin', 'DESC');

        return $qb->getQuery()->getResult();

    }
}
