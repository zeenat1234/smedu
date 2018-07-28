<?php

namespace App\Repository;

use App\Entity\ClassOptional;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ClassOptional|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClassOptional|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClassOptional[]    findAll()
 * @method ClassOptional[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClassOptionalRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ClassOptional::class);
    }

//    /**
//     * @return ClassOptional[] Returns an array of ClassOptional objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ClassOptional
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
