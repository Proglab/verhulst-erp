<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Todo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Todo>
 *
 * @method Todo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Todo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Todo[]    findAll()
 * @method Todo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TodoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Todo::class);
    }

    public function save(Todo $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Todo $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllDone(): array
    {
        return $this->createQueryBuilder('t')
        ->andWhere('t.done = :done')
        ->setParameter('done', true)
        ->orderBy('t.date_reminder', 'ASC')
        ->getQuery()
        ->getResult()
        ;
    }

    public function findAllNoDone(): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.done = :done')
            ->setParameter('done', false)
            ->orderBy('t.date_reminder', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function countAllDone(): int
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id) as count')
            ->andWhere('t.done = :done')
            ->setParameter('done', true)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function countAllNoDone(): int
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id) as count')
            ->andWhere('t.done = :done')
            ->setParameter('done', false)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
