<?php

namespace App\Repository;

use App\Entity\Vehicle\Vehicle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Vehicle|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vehicle|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vehicle[]    findAll()
 * @method Vehicle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VehicleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehicle::class);
    }

    public function findByClassAndName(string $class, string $model): array
    {
        $result = $this->createQueryBuilder('v')
            ->andWhere("v INSTANCE OF $class")
            ->andWhere('v.model = :model')
            ->setParameter('model', $model)
            ->getQuery()
            ->getResult();

        return $result;
    }
}
