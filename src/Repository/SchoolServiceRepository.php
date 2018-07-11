<?php

namespace App\Repository;

use App\Entity\SchoolService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SchoolService|null find($id, $lockMode = null, $lockVersion = null)
 * @method SchoolService|null findOneBy(array $criteria, array $orderBy = null)
 * @method SchoolService[]    findAll()
 * @method SchoolService[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchoolServiceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SchoolService::class);
    }

//    /**
//     * @return SchoolService[] Returns an array of SchoolService objects
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
    public function findOneBySomeField($value): ?SchoolService
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
