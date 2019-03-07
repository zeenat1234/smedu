<?php

namespace App\Repository;

use App\Entity\OptionalEnrollRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method OptionalEnrollRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method OptionalEnrollRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method OptionalEnrollRequest[]    findAll()
 * @method OptionalEnrollRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OptionalEnrollRequestRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, OptionalEnrollRequest::class);
    }

//    /**
//     * @return OptionalEnrollRequest[] Returns an array of OptionalEnrollRequest objects
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
    public function findOneBySomeField($value): ?OptionalEnrollRequest
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
