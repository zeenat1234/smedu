<?php

namespace App\Repository;

use App\Entity\SmartReceipt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SmartReceipt|null find($id, $lockMode = null, $lockVersion = null)
 * @method SmartReceipt|null findOneBy(array $criteria, array $orderBy = null)
 * @method SmartReceipt[]    findAll()
 * @method SmartReceipt[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SmartReceiptRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SmartReceipt::class);
    }

    public function findLatestBySerial($serial): ?SmartReceipt
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.receiptSerial = :val')
            ->setParameter('val', $serial)
            ->orderBy('a.receiptNumber', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

//    /**
//     * @return SmartReceipt[] Returns an array of SmartReceipt objects
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
    public function findOneBySomeField($value): ?SmartReceipt
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
