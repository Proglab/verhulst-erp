<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CompanyContact;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CompanyContact>
 *
 * @method CompanyContact|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanyContact|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanyContact[]    findAll()
 * @method CompanyContact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompanyContact::class);
    }

    public function save(CompanyContact $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CompanyContact $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getUpdatedContact(User $user): ?array
    {
        $contacts = $this->createQueryBuilder('c')
            ->andWhere('c.added_by = :user')
            ->setParameter('user', $user)
            ->andWhere('c.updated_dt IS NOT NULL')
            ->andWhere('c.email IS NOT NULL')
            ->andWhere('c.email != \'\'')
            ->getQuery()
            ->getResult();

        return $contacts;
    }

    public function search(string $query, ?int $addedBy = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.company', 'company');

        if (null !== $addedBy) {
            $qb->where('c.added_by = :addedBy')
                ->setParameter('addedBy', $addedBy);
        }

        $words = preg_split('/\s+/', trim($query));

        foreach ($words as $index => $word) {
            $param = "word{$index}";
            $likeExpr = "%{$word}%";
            $orX = $qb->expr()->orX();
            $orX->add($qb->expr()->like('c.firstname', ":$param"));
            $orX->add($qb->expr()->like('c.lastname', ":$param"));
            $orX->add($qb->expr()->like('c.email', ":$param"));
            $orX->add($qb->expr()->like('company.name', ":$param"));

            $qb->setParameter($param, $likeExpr);

            $qb->andWhere($orX);
        }

        return $qb->getQuery()->getResult();
    }
}
