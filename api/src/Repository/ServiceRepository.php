<?php

namespace App\Repository;

use App\Entity\Service\Service;
use App\Entity\Workflow\State;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Service|null find($id, $lockMode = null, $lockVersion = null)
 * @method Service|null findOneBy(array $criteria, array $orderBy = null)
 * @method Service[]    findAll()
 * @method Service[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Service::class);
    }

    public function findByClassAndName(string $class, string $name): array
    {
        $result = $this->createQueryBuilder('v')
            ->andWhere("v INSTANCE OF $class")
            ->andWhere('v.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getResult();

        return $result;
    }
}
