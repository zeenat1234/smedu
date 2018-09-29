<?php

namespace App\Repository;

use App\Entity\MonthAccount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MonthAccount|null find($id, $lockMode = null, $lockVersion = null)
 * @method MonthAccount|null findOneBy(array $criteria, array $orderBy = null)
 * @method MonthAccount[]    findAll()
 * @method MonthAccount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MonthAccountRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MonthAccount::class);
    }

    /**
     * @return MonthAccount[] Returns an array of MonthAccount objects
     */

     public function findByStudent($studId)
     {
        return $this->createQueryBuilder('acc')
             ->andWhere('acc.student = :val')
             ->setParameter('val', $studId)
             ->addOrderBy('acc.accYearMonth', 'ASC')
             ->getQuery()
             ->getResult()
        ;
      }

//    /**
//     * @return MonthAccount[] Returns an array of MonthAccount objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MonthAccount
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
