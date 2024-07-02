<?php

declare(strict_types=1);

namespace App\Repository\Budget;

use App\Entity\Budget\Invoice;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Invoice>
 *
 * @method Invoice|null find($id, $lockMode = null, $lockVersion = null)
 * @method Invoice|null findOneBy(array $criteria, array $orderBy = null)
 * @method Invoice[]    findAll()
 * @method Invoice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    public function getInvoiceToValidate(User $user): bool|float|int|string|null
    {
        $qb = $this->createQueryBuilder('i');
        $qb->select('count(i.id)')
            ->andWhere('i.validated_user = :user')
            ->setParameter('user', $user)
            ->andWhere('i.validated = :validated')
            ->setParameter('validated', false)
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }
}
