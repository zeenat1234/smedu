<?php

namespace App\Repository;

use App\Entity\SchoolUnit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

use App\Entity\SchoolYear;

/**
 * @method SchoolUnit|null find($id, $lockMode = null, $lockVersion = null)
 * @method SchoolUnit|null findOneBy(array $criteria, array $orderBy = null)
 * @method SchoolUnit[]    findAll()
 * @method SchoolUnit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchoolUnitRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SchoolUnit::class);
    }

    /**
     * @return SchoolUnit[] Returns an array of SchoolUnit objects
     */

    public function findCurrentUnits($startdate, $enddate)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.start_date >= :val1')
            ->andWhere('s.end_date <= :val2')
            ->setParameter('val1', $startdate)
            ->setParameter('val2', $enddate)
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }



//    /**
//     * @return SchoolUnit[] Returns an array of SchoolUnit objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SchoolUnit
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
