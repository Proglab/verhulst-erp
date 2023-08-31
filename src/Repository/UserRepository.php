<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function getCommercials(): array
    {
        return $this->getCommercialsQb()
            ->getQuery()
            ->getResult()
        ;
    }

    public function getCommercialsQb(): QueryBuilder
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_COMMERCIAL%')
        ;
    }

    public function getUserAdminBudget(): QueryBuilder
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.roles = :role')
            ->setParameter('role', 'ROLE_ADMIN_BUDGET')
        ;
    }

    public function getStatsByUser(\DateTime $startDate, \DateTime $endDate): array
    {
        $sql = 'SELECT SUM((sales.price * sales.quantity) - sales.discount) as total, user.first_name, user.last_name, user.id
FROM `user`
LEFT JOIN sales ON (sales.user_id = user.id AND sales.date >= "' . $startDate->format('Y-m-d') . '" AND sales.date <= "' . $endDate->format('Y-m-d') . '")
WHERE `user`.`roles` LIKE "%COMMERCIAL%"
GROUP BY user.id';

        $query = $this->getEntityManager()->getConnection()->executeQuery($sql);
        $result = $query->fetchAllAssociative();

        return $result;
    }
}
