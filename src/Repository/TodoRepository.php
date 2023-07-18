<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Todo;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

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

    public function findAllNoDoneTodayByUser(User $user): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.done = :done')
            ->setParameter('done', false)
            ->andWhere('t.date_reminder <= :date')
            ->setParameter('date', (new \DateTime('now'))->format('Y-m-d') . ' 23:23:59')
            ->andWhere('t.user = :user')
            ->setParameter('user', $user)
            ->orderBy('t.date_reminder', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllNoDoneToday(): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.done = :done')
            ->setParameter('done', false)
            ->andWhere('t.date_reminder <= :date')
            ->setParameter('date', (new \DateTime('now'))->format('Y-m-d') . ' 23:23:59')
            ->orderBy('t.date_reminder', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function countAllNoDoneTodayByUser(User $user): int
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id) as count')
            ->andWhere('t.done = :done')
            ->setParameter('done', false)
            ->andWhere('t.date_reminder <= :date')
            ->setParameter('date', (new \DateTime('now'))->format('Y-m-d'))
            ->andWhere('t.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findAllTodayByUser(UserInterface $user): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.date_reminder <= :date')
            ->setParameter('date', (new \DateTime('now'))->format('Y-m-d') . ' 23:23:59')
            ->andWhere('t.user = :user')
            ->setParameter('user', $user)
            ->orderBy('t.date_reminder', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllToday(): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.date_reminder <= :date')
            ->setParameter('date', (new \DateTime('now'))->format('Y-m-d') . ' 23:23:59')
            ->orderBy('t.date_reminder', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findNext10(): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.date_reminder >= :date')
            ->setParameter('date', (new \DateTime('now'))->format('Y-m-d') . ' 00:00:00')
            ->orderBy('t.date_reminder', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findNext10ByUser(UserInterface $user): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.date_reminder >= :date')
            ->setParameter('date', (new \DateTime('now'))->format('Y-m-d') . ' 00:00:00')
            ->andWhere('t.user = :user')
            ->setParameter('user', $user)
            ->orderBy('t.date_reminder', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
}
