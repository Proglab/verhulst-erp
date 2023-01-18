<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ProductSponsoring;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductSponsoring>
 *
 * @method ProductSponsoring|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductSponsoring|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductSponsoring[]    findAll()
 * @method ProductSponsoring[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductSponsoringRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductSponsoring::class);
    }

    public function save(ProductSponsoring $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductSponsoring $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
