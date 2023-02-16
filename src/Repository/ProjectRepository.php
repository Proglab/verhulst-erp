<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
    public function __construct(ManagerRegistry $registry)
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
            ->addSelect('event, package, sponsoring, divers')
            ->leftJoin('p.product_event', 'event')
            ->leftJoin('p.product_package', 'package')
            ->leftJoin('p.product_sponsoring', 'sponsoring')
            ->leftJoin('p.product_divers', 'divers')
            ->where('p.name LIKE :search')
            ->orWhere('event.name LIKE :search')
            ->orWhere('package.name LIKE :search')
            ->orWhere('sponsoring.name LIKE :search')
            ->orWhere('divers.name LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->getQuery()
            ->getResult();
    }
}
