<?php

namespace App\Repository;

use App\Entity\TransportTrip;
use App\Entity\Student;
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

    public function getTransportPrices (Student $student, \DateTimeInterface $start, \DateTimeInterface $end)
    {
        $sql = "SELECT
            SUM(
                CASE
                    WHEN trip_type = 1 THEN distance1 * price
                    WHEN trip_type = 2 THEN distance2 * price
                    ELSE (distance1 + distance2) * price 
                END
            ) as variablePrice,
            SUM(
                CASE 
                    WHEN price_per_km = 0 THEN price
                END
            ) as fixedPrice
            FROM `transport_trip`
            WHERE `date` >= :startDate AND `date` <= :endDate AND student_id = :studentId";

        $params = [
            'startDate' => $start->format('Y-m-d H:i:s'),
            'endDate'   => $end->format('Y-m-d H:i:s'),
            'studentId' => $student->getId()
        ];

        return $this->getEntityManager()
            ->getConnection()
            ->executeQuery($sql, $params)
            ->fetch();
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
