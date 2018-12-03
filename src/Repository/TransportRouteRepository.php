<?php

namespace App\Repository;

use App\Entity\TransportRoute;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TransportRoute|null find($id, $lockMode = null, $lockVersion = null)
 * @method TransportRoute|null findOneBy(array $criteria, array $orderBy = null)
 * @method TransportRoute[]    findAll()
 * @method TransportRoute[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransportRouteRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TransportRoute::class);
    }

//    /**
//     * @return TransportRoute[] Returns an array of TransportRoute objects
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
    public function findOneBySomeField($value): ?TransportRoute
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
