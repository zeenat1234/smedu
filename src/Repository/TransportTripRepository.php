<?php

namespace App\Repository;

use App\Entity\TransportTrip;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TransportTrip|null find($id, $lockMode = null, $lockVersion = null)
 * @method TransportTrip|null findOneBy(array $criteria, array $orderBy = null)
 * @method TransportTrip[]    findAll()
 * @method TransportTrip[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransportTripRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TransportTrip::class);
    }

    /**
     * @return TransportTrip[] Returns an array of TransportTrip objects
     */
    public function findAllForStudByInterval(\DateTimeInterface $start, $end, $student)
    {
        return $this->createQueryBuilder('tr')
            ->andWhere('tr.student = :theStudent')
            ->setParameter('theStudent', $student->getId())
            ->andWhere('tr.date >= :firstDay')
            ->andWhere('tr.date <= :lastDay')
            ->setParameter('firstDay', $start->format('Y-m-d H:i:s'))
            ->setParameter('lastDay', $end->format('Y-m-d H:i:s'))
            ->orderBy('tr.date', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

//    /**
//     * @return TransportTrip[] Returns an array of TransportTrip objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TransportTrip
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
