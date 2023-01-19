<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ProductDivers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductDivers>
 *
 * @method ProductDivers|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductDivers|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductDivers[]    findAll()
 * @method ProductDivers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductDiversRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductDivers::class);
    }

    public function save(ProductDivers $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductDivers $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
