<?php

namespace App\Repository;

use App\Entity\OptionalsAttendance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method OptionalsAttendance|null find($id, $lockMode = null, $lockVersion = null)
 * @method OptionalsAttendance|null findOneBy(array $criteria, array $orderBy = null)
 * @method OptionalsAttendance[]    findAll()
 * @method OptionalsAttendance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OptionalsAttendanceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, OptionalsAttendance::class);
    }

//    /**
//     * @return OptionalsAttendance[] Returns an array of OptionalsAttendance objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OptionalsAttendance
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
