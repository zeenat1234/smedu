<?php

namespace App\Repository;

use App\Entity\Student;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Student|null find($id, $lockMode = null, $lockVersion = null)
 * @method Student|null findOneBy(array $criteria, array $orderBy = null)
 * @method Student[]    findAll()
 * @method Student[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StudentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Student::class);
    }

    /**
     * @return Student[] Returns an array of Student objects
     */
     public function findAllYear($yearId)
     {
        return $this->createQueryBuilder('s')
             ->leftJoin('s.User', 'usr')
             ->leftJoin('s.schoolUnit', 'unit')
             ->andWhere('unit.schoolyear = :val')
             ->setParameter('val', $yearId)
             ->addOrderBy('usr.lastName', 'ASC')
             ->addOrderBy('usr.firstName', 'ASC')
             ->getQuery()
             ->getResult()
        ;
      }

    /**
     * @return Student[] Returns an array of active students
     */
      public function findAllActiveStudents($yearId)
      {
        return $this->getEntityManager()->createQueryBuilder()
             ->select('s')
             ->from('App:Student', 's')
             ->innerJoin('App:User', 'u', 'WITH', 's.User = u.id')
             ->innerJoin('App:SchoolUnit', 'su', 'WITH', 's.schoolUnit = su.id AND su.schoolyear = :val')
             ->innerJoin('App:Enrollment', 'e', 'WITH', 'e.student = s.id AND e.isActive = 1')
             ->setParameter('val', $yearId);
             //->addOrderBy('u.lastName', 'ASC')
             //->addOrderBy('u.firstName', 'ASC')
             //->getQuery();
             //->getResult();
      }


     /**
      * @return Student[] Returns an array of Student objects
      */
      public function findAllUnit($unitId)
      {
        return $this->createQueryBuilder('s')
             ->leftJoin('s.User', 'usr')
             ->leftJoin('s.schoolUnit', 'unit')
             ->andWhere('unit.id = :val')
             ->setParameter('val', $unitId)
             ->addOrderBy('usr.lastName', 'ASC')
             ->addOrderBy('usr.firstName', 'ASC')
             ->getQuery()
             ->getResult()
        ;
      }


      /**
      * Find all students except selected ones
      * @param array $excludedStudents Array of excluded students
      **/
      public function findAllStudentsExcept($excludedStudents)
      {
        $excludedIds = [];
        foreach($excludedStudents as $student){
            $excludedIds[] = $student->getId();
        }
        return $this->createQueryBuilder('s')
            ->where('s.id NOT IN (:exclIds)')
            ->setParameter('exclIds', implode(',', $excludedIds))
            ->getQuery()
            ->getResult();
      }

//    /**
//     * @return Student[] Returns an array of Student objects
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
    public function findOneBySomeField($value): ?Student
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
