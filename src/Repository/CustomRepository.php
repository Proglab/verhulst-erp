<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Commission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

class CustomRepository
{
    public function __construct(private EntityManagerInterface $manager)
    {

    }

    public function getContact()
    {

    }
}
