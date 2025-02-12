<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ProductPackageVip;
use App\Entity\ProductSponsoring;
use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 *
 * @method Project|null find($id, $lockMode = null, $lockVersion = null)
 * @method Project|null findOneBy(array $criteria, array $orderBy = null)
 * @method Project[]    findAll()
 * @method Project[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(protected ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    public function save(Project $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Project $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function search(string $search): array
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->addSelect('products')
            ->leftJoin('p.products', 'products')
            ->where('p.name LIKE :search')
            ->orWhere('event.name LIKE :search')
            ->orWhere('package.name LIKE :search')
            ->orWhere('sponsoring.name LIKE :search')
            ->orWhere('divers.name LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->getQuery()
            ->getResult();
    }

    public function findAllByYear(int $year): array
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->addSelect('products')
            ->leftJoin('p.products', 'products')
            ->where('p.date_begin >= :dateBegin')
            ->setParameter('dateBegin', new \DateTime($year . '-01-01'))
            ->andWhere('p.date_end <= :dateEnd')
            ->setParameter('dateEnd', new \DateTime($year . '-12-31'))
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findProjectsQb(?string $query, ?\DateTime $from, ?\DateTime $to, ?bool $archived, ?string $type): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p');
        $qb->leftJoin('p.products', 'products');
        $qb->addSelect('products');

        if ($query) {
            $qb->where('(p.name LIKE :query')
                ->setParameter('query', '%' . $query . '%');
            $qb->orWhere('products.name LIKE :query)')
                ->setParameter('query', '%' . $query . '%');
        }
        if ($from && '00:00:000000' === $from->format('i:s:u')) {
            $qb->andWhere('p.date_begin >= :from')
                ->setParameter('from', $from);
        }
        if ($to && '00:00:000000' === $to->format('i:s:u')) {
            $qb->andWhere('p.date_end <= :to')
                ->setParameter('to', $to);
        }

        if ($archived) {
            $qb->andWhere('p.archive = :archive')
                ->setParameter('archive', $archived);
        } else {
            $qb->andWhere('p.archive = false');
        }

        if ($type) {
            $em = $this->registry->getManager();
            $qb->andWhere('products INSTANCE OF :type')
                ->setParameter('type', '1' === $type ? $em->getClassMetadata(ProductSponsoring::class) : $em->getClassMetadata(ProductPackageVip::class));
        }

        $qb->orderBy('p.date_begin', 'ASC');

        return $qb;
    }

    public function findAllNewProjects(): array
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->where('p.new = true')
            ->getQuery()
            ->getResult();
    }
}
