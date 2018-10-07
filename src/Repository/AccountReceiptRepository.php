<?php

namespace App\Repository;

use App\Entity\AccountReceipt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AccountReceipt|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccountReceipt|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccountReceipt[]    findAll()
 * @method AccountReceipt[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountReceiptRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AccountReceipt::class);
    }

    public function findLatestBySerial($serial): ?AccountReceipt
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

    /*
    public function findOneBySomeField($value): ?AccountReceipt
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
