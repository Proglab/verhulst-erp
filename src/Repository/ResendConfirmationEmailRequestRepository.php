<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ResendConfirmationEmailRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ResendConfirmationEmailRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResendConfirmationEmailRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResendConfirmationEmailRequest[]    findAll()
 * @method ResendConfirmationEmailRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResendConfirmationEmailRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResendConfirmationEmailRequest::class);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findToken(string $token): ?ResendConfirmationEmailRequest
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.hashedToken = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function removeExpiredResetConfirmationEmailRequests(): int
    {
        $time = new \DateTimeImmutable('-1 week');
        $query = $this->createQueryBuilder('r')
            ->delete()
            ->where('r.expiresAt <= :time')
            ->setParameter('time', $time)
            ->getQuery()
        ;

        return $query->execute();
    }
}
