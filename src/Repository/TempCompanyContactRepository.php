<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CompanyContact;
use App\Entity\TempCompanyContact;
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
class TempCompanyContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TempCompanyContact::class);
    }

    public function save(TempCompanyContact $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TempCompanyContact $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
