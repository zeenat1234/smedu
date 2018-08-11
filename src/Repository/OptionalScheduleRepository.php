<?php

namespace App\Repository;

use App\Entity\OptionalSchedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method OptionalSchedule|null find($id, $lockMode = null, $lockVersion = null)
 * @method OptionalSchedule|null findOneBy(array $criteria, array $orderBy = null)
 * @method OptionalSchedule[]    findAll()
 * @method OptionalSchedule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OptionalScheduleRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, OptionalSchedule::class);
    }

//    /**
//     * @return OptionalSchedule[] Returns an array of OptionalSchedule objects
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
    public function findOneBySomeField($value): ?OptionalSchedule
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
