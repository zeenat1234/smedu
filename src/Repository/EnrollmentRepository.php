<?php

namespace App\Repository;

use App\Entity\Enrollment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Enrollment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Enrollment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Enrollment[]    findAll()
 * @method Enrollment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EnrollmentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Enrollment::class);
    }

   /**
    * @return Enrollment[] Returns an array of Enrollment objects
    */
    public function findLatest($value, $yearId)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.schoolYear = :val')
            ->setParameter('val', $yearId)
            ->orderBy('e.id', 'DESC')
            ->setMaxResults($value)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Enrollment[] Returns an array of Enrollment objects
     */

     public function findAllYear($yearId)
     {
        return $this->createQueryBuilder('e')
             ->leftJoin('e.idChild', 'u')
             ->andWhere('e.schoolYear = :val')
             ->setParameter('val', $yearId)
             ->addOrderBy('u.lastName', 'ASC')
             ->getQuery()
             ->getResult();
      }

      public function findLatestForChild($yearId, $idChild): ?Enrollment
      {
        return $this->createQueryBuilder('e')
             ->andWhere('e.schoolYear = :val')
             ->andWhere('e.idChild =:vall')
             ->setParameter('val', $yearId)
             ->setParameter('vall', $idChild)
             ->orderBy('e.enrollDate', 'DESC')
             ->setMaxResults(1)
             ->getQuery()
             ->getOneOrNullResult();
      }



//    /**
//     * @return Enrollment[] Returns an array of Enrollment objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Enrollment
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
